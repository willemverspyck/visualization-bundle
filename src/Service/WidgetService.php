<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Countable;
use DateTimeInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use IteratorAggregate;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Spyck\ApiExtension\Model\Pagination;
use Spyck\VisualizationBundle\Entity\Block;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Entity\Widget;
use Spyck\VisualizationBundle\Exception\ParameterException;
use Spyck\VisualizationBundle\Filter\EntityFilterInterface;
use Spyck\VisualizationBundle\Filter\FilterInterface;
use Spyck\VisualizationBundle\Filter\LimitFilter;
use Spyck\VisualizationBundle\Filter\OffsetFilter;
use Spyck\VisualizationBundle\Filter\OptionFilterInterface;
use Spyck\VisualizationBundle\Model\Block as BlockAsModel;
use Spyck\VisualizationBundle\Model\Callback;
use Spyck\VisualizationBundle\Model\Dashboard as DashboardAsModel;
use Spyck\VisualizationBundle\Model\Field;
use Spyck\VisualizationBundle\Model\RouteForDashboard;
use Spyck\VisualizationBundle\Model\RouteInterface;
use Spyck\VisualizationBundle\Model\Widget as WidgetAsModel;
use Spyck\VisualizationBundle\Parameter\DayEndParameter;
use Spyck\VisualizationBundle\Parameter\DayParameter;
use Spyck\VisualizationBundle\Parameter\DayStartParameter;
use Spyck\VisualizationBundle\Parameter\EntityParameterInterface;
use Spyck\VisualizationBundle\Parameter\MonthEndParameter;
use Spyck\VisualizationBundle\Parameter\MonthStartParameter;
use Spyck\VisualizationBundle\Parameter\ParameterInterface;
use Spyck\VisualizationBundle\Parameter\WeekEndParameter;
use Spyck\VisualizationBundle\Parameter\WeekStartParameter;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Spyck\VisualizationBundle\Repository\WidgetRepository;
use Spyck\VisualizationBundle\Request\MultipleRequestInterface;
use Spyck\VisualizationBundle\Request\RequestInterface;
use Spyck\VisualizationBundle\Utility\BlockUtility;
use Spyck\VisualizationBundle\Utility\CacheUtility;
use Spyck\VisualizationBundle\Utility\DateTimeUtility;
use Spyck\VisualizationBundle\View\ViewInterface;
use Spyck\VisualizationBundle\Widget\WidgetInterface;
use Stringable;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AutoconfigureTag('monolog.logger', ['channel' => 'spyck_visualization'])]
readonly class WidgetService
{
    /**
     * @param Countable&IteratorAggregate $widgets
     */
    public function __construct(#[Autowire(service: 'spyck.visualization.config.cache.adapter')] private CacheInterface $cache, private DashboardRepository $dashboardRepository, private readonly LoggerInterface $logger, private RepositoryService $repositoryService, private RequestStack $requestStack, private RouterInterface $router, private TranslatorInterface $translator, private UserService $userService, private UrlGeneratorInterface $urlGenerator, private WidgetRepository $widgetRepository, #[Autowire(param: 'spyck.visualization.config.cache.active')] private bool $cacheActive, #[Autowire(param: 'spyck.visualization.config.request')] private array $request, #[TaggedIterator(tag: 'spyck.visualization.widget')] private iterable $widgets)
    {
    }

    /**
     * Get instance of widget by name.
     *
     * @throws Exception
     * @throws ParameterException
     */
    public function getWidgetInstance(string $name, array $variables = [], bool $fill = false): WidgetInterface
    {
        foreach ($this->widgets->getIterator() as $widget) {
            if (get_class($widget) === $name) {
                $this->setParameters($widget, $variables, $fill);
                $this->setFilters($widget, $variables, $fill);

                return $widget;
            }
        }

        throw new Exception(sprintf('Widget "%s" not found', $name));
    }

    /**
     * @return array<string, WidgetInterface>
     */
    public function getWidgets(): array
    {
        $data = [];

        foreach ($this->widgets->getIterator() as $widget) {
            $data[get_class($widget)] = $widget;
        }

        return $data;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ParameterException
     */
    public function getWidgetDataById(int $id, array $variables = []): DashboardAsModel
    {
        $widget = $this->widgetRepository->getWidgetById($id);

        return $this->getWidgetDataByWidget($widget, $variables);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ParameterException
     */
    public function getWidgetDataByAdapter(string $adapter, array $variables = []): DashboardAsModel
    {
        $widget = $this->widgetRepository->getWidgetByAdapter($adapter);

        return $this->getWidgetDataByWidget($widget, $variables);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ParameterException
     */
    public function getWidgetAsModel(Block $block, array $variables, ?string $view): WidgetAsModel
    {
        $parameterBag = BlockUtility::getParameterBag($block, $variables);

        $widget = $block->getWidget();

        $widgetInstance = $this->getWidgetInstance($widget->getAdapter(), $parameterBag->all());
        $widgetInstance->setWidget($widget);
        $widgetInstance->setView($view);

        return $this->getWidgetData($widgetInstance);
    }

    /**
     * Get the data with a callback.
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getWidgetData(WidgetInterface $widgetInstance): WidgetAsModel
    {
        $fields = $this->filterFields($widgetInstance);

        foreach ($fields as $field) {
            foreach ($field->getRoutes() as $route) {
                $this->setRoute($route);
            }
        }

        $data = $this->getDataWithCache($widgetInstance, $fields);
        $total = $widgetInstance->getTotal();
        $totalIncluded = null === $total;

        if (null === $total) {
            $total = iterator_count($data);
        }

        $widgetModel = new WidgetAsModel();
        $widgetModel->setFields($this->getFields($fields));
        $widgetModel->setData($data);
        $widgetModel->setTotal($total);
        $widgetModel->setEvents($widgetInstance->getEvents());
        $widgetModel->setProperties($widgetInstance->getProperties());
        $widgetModel->setParameters($this->getParameters($widgetInstance));
        $widgetModel->setFilters($this->getFilters($widgetInstance));
        $widgetModel->setPagination($this->getPagination($widgetInstance, $total, $totalIncluded));

        return $widgetModel;
    }

    /**
     * @throws Exception
     * @throws NonUniqueResultException
     */
    public function setRoute(RouteInterface $route): void
    {
        if (false === $route instanceof RouteForDashboard) {
            return;
        }

        $dashboard = $this->dashboardRepository->getDashboardByCode($route->getCode());

        if (null === $dashboard) {
            $this->logger->warning(sprintf('Dashboard not found (%s)', $route->getCode()));

            return;
        }

        if (null === $route->getName()) {
            $route->setName($dashboard->getName());
        }

        $route->setUrl($this->urlGenerator->generate('spyck_visualization_dashboard_show', [
            'dashboardId' => $dashboard->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL));

        $parameters = [];
        $parametersForDashboard = $this->getDashboardParameterData($dashboard);

        $fields = $route->getData();

        $request = $this->requestStack->getCurrentRequest();

        foreach ($parametersForDashboard as $parameter) {
            if ($parameter instanceof EntityParameterInterface) {
                $name = $parameter->getName();

                $parameters[$parameter->getField()] = array_key_exists($name, $fields) ? sprintf('{%s}', $fields[$name]) : $request?->get($parameter->getField());
            }
        }

        $route->setParameters($parameters);
    }

    /**
     * Get required parameters for dashboard.
     *
     * @return array<int, ParameterInterface>
     *
     * @throws Exception
     * @throws ParameterException
     *
     * @todo: Duplicate in DashboardService
     */
    public function getDashboardParameterData(Dashboard $dashboard, array $variables = []): array
    {
        $data = [];

        foreach ($dashboard->getBlocks() as $block) {
            $parameterBag = BlockUtility::getParameterBag($block, $variables);

            $widgetInstance = $this->getWidgetInstance($block->getWidget()->getAdapter(), $parameterBag->all(), true);

            foreach ($widgetInstance->getParameterData() as $parameter) {
                $name = $parameter->getName();

                if (false === array_key_exists($name, $data)) {
                    $data[$name] = $parameter;
                }
            }
        }

        return array_values($data);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ParameterException
     *
     * @todo: setParametersAsString and setParametersAsStringForSlug for unique naming when downloading
     */
    private function getWidgetDataByWidget(?Widget $widget, array $variables = []): DashboardAsModel
    {
        if (null === $widget) {
            throw new NotFoundHttpException('The widget does not exist');
        }

        $currentRequest = $this->requestStack->getCurrentRequest();

        $widgetInstance = $this->getWidgetInstance($widget->getAdapter(), $variables);
        $widgetInstance->setWidget($widget);
        $widgetInstance->setView(null === $currentRequest ? ViewInterface::JSON : $currentRequest->getRequestFormat());

        $block = new BlockAsModel();
        $block->setWidget($this->getWidgetData($widgetInstance));
        $block->setName($widget->getName());
        $block->setDescriptionEmpty($widget->getDescriptionEmpty());
        $block->setCharts($widget->getCharts());

        $user = $this->userService->getUser();

        $dashboard = new DashboardAsModel();
        $dashboard->setUser($user);
        $dashboard->setName($widget->getName());
        $dashboard->addBlock($block);

        return $dashboard;
    }

    /**
     * Set the parameters of a widget.
     *
     * @throws Exception
     * @throws ParameterException
     */
    private function setParameters(WidgetInterface $widget, array $variables, bool $fill = false): void
    {
        $parameters = $this->mapRequest($widget->getParameters(), function (ParameterInterface $parameter) use ($variables, $fill): void {
            $data = $this->getRequestData($parameter, $variables, $fill);

            if (null === $data) {
                if (false === $fill) {
                    throw new ParameterException(sprintf('Parameter "%s" not found', $parameter->getName()));
                }

                return;
            }

            $parameter->setData($data);

            if ($parameter instanceof EntityParameterInterface) {
                $queryBag = new ParameterBag();

                $request = $this->requestStack->getCurrentRequest();

                if (null !== $request) {
                    $queryBag->add($request->query->all());
                }

                $dataAsObject = $this->getEntityById($parameter->getName(), $parameter->getData());

                $parameter->setDataAsObject($dataAsObject);
                $parameter->setRequest($queryBag->has($parameter->getField()));
            }
        });

        $widget->setParameters($parameters);
    }

    /**
     * Set the filters of a widget.
     */
    private function setFilters(WidgetInterface $widget, array $variables, bool $fill = false): void
    {
        $filters = $this->mapRequest($widget->getFilters(), function (FilterInterface $filter) use ($variables, $fill): void {
            $data = $this->getRequestData($filter, $variables, $fill);

            if (null === $data) {
                return;
            }

            $filter->setData(explode(',', $data));

            if ($filter instanceof EntityFilterInterface) {
                $dataAsObject = [];

                foreach ($filter->getData() as $entityId) {
                    $entity = $this->getEntityById($filter->getName(), (int) $entityId);

                    $dataAsObject[] = $entity;
                }

                $filter->setDataAsObject($dataAsObject);
            }
        });

        $widget->setFilters($filters);
    }

    /**
     * @throws Exception
     */
    private function getRequestData(RequestInterface $request, array $variables, bool $fill): ?string
    {
        $parameterBag = new ParameterBag();
        $parameterBag->add($variables);

        $field = $request->getField();

        if ($parameterBag->has($field)) {
            $variable = $parameterBag->get($field);

            $type = gettype($variable);

            if (false === in_array($type, ['integer', 'string'], true)) {
                throw new Exception(sprintf('Request data of field "%s" must be "integer" or "string", not "%s"', $field, $type));
            }

            return sprintf('%s', $variable);
        }

        if (false === $fill) {
            return null;
        }

        return match (get_class($request)) {
            DayParameter::class => $this->request['dayParameter'],
            DayStartParameter::class => $this->request['dayStartParameter'],
            DayEndParameter::class => $this->request['dayEndParameter'],
            WeekStartParameter::class => $this->request['weekStartParameter'],
            WeekEndParameter::class => $this->request['weekEndParameter'],
            MonthStartParameter::class => $this->request['monthStartParameter'],
            MonthEndParameter::class => $this->request['monthEndParameter'],
            LimitFilter::class => sprintf('%d', $this->request['limitFilter']),
            OffsetFilter::class => sprintf('%d', $this->request['offsetFilter']),
            default => null,
        };
    }

    /**
     * Get the entity by id.
     *
     * @throws NotFoundHttpException
     */
    private function getEntityById(string $entityName, int $entityId): object
    {
        $entity = $this->repositoryService->getEntityById($entityName, $entityId);

        if (null === $entity) {
            throw new NotFoundHttpException(sprintf('Parameter or filter "%s" does not exist (%d)', $entityName, $entityId));
        }

        return $entity;
    }

    /**
     * @param array<int, Field> $fields
     */
    private function getFields(array $fields): array
    {
        $columns = [];

        foreach ($fields as $field) {
            $children = [];

            foreach ($field->getChildren() as $child) {
                $children[] = [
                    'name' => $child->getName(),
                    'type' => $child->getType(),
                    'config' => $child->getConfig(),
                ];
            }

            $columns[] = [
                'name' => $field->getName(),
                'type' => $field->getType(),
                'config' => $field->getConfig(),
                'children' => $children,
            ];
        }

        return $columns;
    }

    /**
     * @throws Exception
     */
    private function getData(WidgetInterface $widget, array $fields): array
    {
        $data = [];

        foreach ($widget->getData() as $row) {
            $columnData = [];

            foreach ($fields as $field) {
                $columnData[] = [
                    'value' => $this->getValue($field, $row),
                    'routes' => $this->getRoutes($field, $row),
                    'children' => $this->getChildren($field, $row),
                ];
            }

            $data[] = [
                'fields' => $columnData,
            ];
        }

        return $data;
    }

    private function getDataWithCache(WidgetInterface $widget, array $fields): array
    {
        if (false === $this->cacheActive || null === $widget->getCache()) {
            $this->logger->info('Cache disabled');

            return $this->getData($widget, $fields);
        }

        $key = $this->getCacheKey($widget);

        $data = $this->cache->get($key, function (ItemInterface $item) use ($widget, $fields): array {
            $item->expiresAfter($widget->getCache());

            if ($this->cache instanceof TagAwareCacheInterface) {
                $item->tag(sprintf('spyck_visualization_widget_%s', $widget->getWidget()->getId()));
            }

            return $this->getData($widget, $fields);
        }, null, $metadata);

        $this->logger->info('Cache', [
            'cache' => $widget->getCache(),
            'metadata' => $metadata,
        ]);

        return $data;
    }

    /**
     * Get the data with a callback.
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    private function getCacheKey(WidgetInterface $widgetInstance): string
    {
        $widget = $widgetInstance->getWidget();

        $data = [
            $widget->getAdapter(),
            serialize($widget->getTimestampCreated()),
            serialize($widget->getTimestampUpdated()),
            serialize($widgetInstance->getParameterDataRequest()),
            serialize($widgetInstance->getFilterDataRequest()),
        ];

        return CacheUtility::getCacheKey(__CLASS__, $data);
    }

    /**
     * @throws Exception
     */
    private function getParameters(WidgetInterface $widget): array
    {
        $content = [];

        $parameters = $widget->getParameterData();

        foreach ($parameters as $parameter) {
            if ($parameter instanceof EntityParameterInterface) {
                $data = $parameter->getDataAsObject();

                if (null !== $data) {
                    if (false === $data instanceof Stringable) {
                        throw new Exception(sprintf('Object "%s" must be instance of "%s"', get_class($data), Stringable::class));
                    }

                    $content[] = [
                        'name' => $parameter->getName(),
                        'data' => [
                            $data->__toString(),
                        ],
                    ];
                }
            }
        }

        return $content;
    }

    /**
     * @throws Exception
     */
    private function getFilters(WidgetInterface $widget): array
    {
        $content = [];

        $filters = $widget->getFilterData();

        foreach ($filters as $filter) {
            if ($filter instanceof EntityFilterInterface) {
                $data = $filter->getDataAsObject();

                if (null !== $data) {
                    $content[] = [
                        'name' => $this->translator->trans(id: sprintf('filter.%s.name', $filter->getName()), domain: 'SpyckVisualizationBundle'),
                        'data' => array_map(function (object $entity): string {
                            if (false === $entity instanceof Stringable) {
                                throw new Exception(sprintf('Object "%s" must be instance of "%s"', get_class($entity), Stringable::class));
                            }

                            return $entity->__toString();
                        }, $data),
                    ];
                }
            }

            if ($filter instanceof OptionFilterInterface) {
                $data = $filter->getDataAsOptions();

                if (null !== $data) {
                    $content[] = [
                        'name' => $this->translator->trans(id: sprintf('filter.%s.name', $filter->getName()), domain: 'SpyckVisualizationBundle'),
                        'data' => $data,
                    ];
                }
            }
        }

        return $content;
    }

    private function filterFields(WidgetInterface $widgetInstance): array
    {
        $fields = iterator_to_array($widgetInstance->getFields());

        return array_filter($fields, function (Field $field) use ($widgetInstance): bool {
            $filter = $field->getFilter();

            if (null === $filter) {
                return true;
            }

            return call_user_func($filter->getName(), $filter->getParameters(), $widgetInstance);
        });
    }

    /**
     * @return array<int, RequestInterface>
     */
    private function mapRequest(iterable $parameters, callable $callback): array
    {
        $data = [];

        foreach ($parameters as $parameter) {
            if ($parameter instanceof MultipleRequestInterface) {
                foreach ($parameter->getChildren() as $child) {
                    $callback($child);

                    $data[get_class($child)] = $child;
                }
            } else {
                $callback($parameter);

                $data[get_class($parameter)] = $parameter;
            }
        }

        return $data;
    }

    /**
     * Get the data of the route.
     */
    private function getPagination(WidgetInterface $widget, int $total, bool $totalIncluded): ?Pagination
    {
        $pagination = $widget->getPagination();

        if (null === $pagination) {
            return null;
        }

        $name = 'spyck_visualization_widget_item';

        $parameters = $this->getPaginationParameters($name);

        if (null === $parameters) {
            return null;
        }

        $next = null;

        if ($totalIncluded ? $total >= $pagination['limit'] : $total > $pagination['limit']) {
            $next = $this->urlGenerator->generate($name, array_merge($parameters, [
                'limit' => $pagination['limit'],
                'offset' => $pagination['offset'] + $pagination['limit'],
            ]), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $previous = null;

        if ($pagination['offset'] - $pagination['limit'] >= 0) {
            $previous = $this->urlGenerator->generate($name, array_merge($parameters, [
                'limit' => $pagination['limit'],
                'offset' => $pagination['offset'] - $pagination['limit'],
            ]), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $pagination = new Pagination();
        $pagination->setPrevious($previous);
        $pagination->setNext($next);

        return $pagination;
    }

    private function getPaginationParameters(string $name): ?array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return null;
        }

        $routeCollection = $this->router->getRouteCollection();

        $route = $routeCollection->get($name);

        if (null === $route) {
            return null;
        }

        $parameters = array_merge($request->query->all(), $request->attributes->get('_route_params', []));

        $variables = $route->compile()->getVariables();

        if (count(array_diff($variables, array_keys($parameters))) > 0) {
            return null;
        }

        return $parameters;
    }

    /**
     * @throws Exception
     */
    private function getValue(Field $field, array $data): array|bool|DateTimeInterface|float|int|string|null
    {
        $source = $field->getSource();

        if ($source instanceof Callback) {
            return call_user_func($source->getName(), $data, $source->getParameters());
        }

        if (false === array_key_exists($source, $data)) {
            return null;
        }

        $value = $data[$source];

        if (null === $value) {
            return null;
        }

        return match ($field->getType()) {
            Field::TYPE_CURRENCY, Field::TYPE_NUMBER, Field::TYPE_PERCENTAGE => (float) $value,
            Field::TYPE_DATE => $value instanceof DateTimeInterface ? $value : DateTimeUtility::getDateFromString($value),
            Field::TYPE_DATETIME => $value instanceof DateTimeInterface ? $value : DateTimeUtility::getDateTimeFromString($value),
            Field::TYPE_TIME => $value instanceof DateTimeInterface ? $value : DateTimeUtility::getTimeFromString($value),
            default => $value,
        };
    }

    private function getRoutes(Field $field, array $data = []): array
    {
        $content = [];

        foreach ($field->getRoutes() as $route) {
            $url = $this->getRouteUrl($route, $data);

            if (null !== $url) {
                $content[] = [
                    'name' => $route->getName(),
                    'url' => $url,
                ];
            }
        }

        return $content;
    }

    /**
     * @throws Exception
     */
    private function getRouteUrl(RouteInterface $route, array $data): ?string
    {
        if (null === $route->getName() || null === $route->getUrl()) {
            return null;
        }

        $query = [];

        $parameters = $route->getParameters();

        foreach ($parameters as $name => $value) {
            if (null === $value) {
                return null;
            }

            if (1 === preg_match('/{([\w]+)}/', $value, $matches)) {
                $key = $matches[1];

                if (false === array_key_exists($key, $data)) {
                    throw new Exception(sprintf('Route parameter "%s" not found in data', $key));
                }

                $value = $data[$key];

                if (null === $value) {
                    return null;
                }
            }

            $query[$name] = $value;
        }

        $url = $route->getUrl();

        return sprintf('%s?%s', $url, http_build_query($query));
    }

    /**
     * Get the overlay data of the route.
     *
     * @throws Exception
     */
    private function getChildren(Field $field, array $data): array
    {
        $content = [];

        foreach ($field->getChildren() as $child) {
            $content[] = [
                'value' => $this->getValue($child, $data),
            ];
        }

        return $content;
    }
}
