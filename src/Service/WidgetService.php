<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Countable;
use Doctrine\ORM\NonUniqueResultException;
use IteratorAggregate;
use Psr\Cache\InvalidArgumentException;
use Spyck\ApiExtension\Model\Pagination;
use Spyck\VisualizationBundle\Entity\Block;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Entity\Widget;
use Spyck\VisualizationBundle\Exception\ParameterException;
use Spyck\VisualizationBundle\Filter\LimitFilter;
use Spyck\VisualizationBundle\Filter\OffsetFilter;
use Spyck\VisualizationBundle\Model\Block as BlockAsModel;
use Spyck\VisualizationBundle\Model\Callback;
use Spyck\VisualizationBundle\Model\Dashboard as DashboardAsModel;
use Spyck\VisualizationBundle\Model\Field;
use Spyck\VisualizationBundle\Model\RouteForDashboard;
use Spyck\VisualizationBundle\Model\RouteInterface;
use Spyck\VisualizationBundle\Model\Widget as WidgetAsModel;
use Spyck\VisualizationBundle\Parameter\DateParameterInterface;
use Spyck\VisualizationBundle\Parameter\DayEndParameter;
use Spyck\VisualizationBundle\Parameter\DayParameter;
use Spyck\VisualizationBundle\Parameter\DayStartParameter;
use Spyck\VisualizationBundle\Parameter\MonthEndParameter;
use Spyck\VisualizationBundle\Parameter\MonthStartParameter;
use Spyck\VisualizationBundle\Parameter\WeekEndParameter;
use Spyck\VisualizationBundle\Parameter\WeekStartParameter;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Spyck\VisualizationBundle\Repository\WidgetRepository;
use Spyck\VisualizationBundle\Utility\BlockUtility;
use Spyck\VisualizationBundle\Utility\CacheUtility;
use Spyck\VisualizationBundle\Utility\DateTimeUtility;
use Spyck\VisualizationBundle\View\ViewInterface;
use Spyck\VisualizationBundle\Widget\WidgetInterface;
use Spyck\VisualizationBundle\Request\MultipleRequestInterface;
use Spyck\VisualizationBundle\Request\RequestInterface;
use Spyck\VisualizationBundle\Filter\EntityFilterInterface;
use Spyck\VisualizationBundle\Filter\FilterInterface;
use Spyck\VisualizationBundle\Filter\OptionFilter;
use Spyck\VisualizationBundle\Parameter\EntityParameterInterface;
use Spyck\VisualizationBundle\Parameter\ParameterInterface;
use DateTimeInterface;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class WidgetService
{
    /**
     * @param Countable&IteratorAggregate $widgets
     */
    public function __construct(private CacheInterface $cache, private DashboardRepository $dashboardRepository, private ImageService $imageService, private RepositoryService $repositoryService, private RequestStack $requestStack, private RouterInterface $router, private TranslatorInterface $translator, private UserService $userService, private UrlGeneratorInterface $urlGenerator, private WidgetRepository $widgetRepository, #[Autowire(param: 'spyck.visualization.cache')] private bool $hasCache, #[Autowire(param: 'spyck.visualization.request')] private array $request, #[TaggedIterator(tag: 'spyck.visualization.widget')] private iterable $widgets)
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
    public function getWidgetAsModel(Block $block, array $variables, string $view): WidgetAsModel
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
    public function getCacheKey(WidgetInterface $widgetInstance): string
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

        if ($this->hasCache) {
            $key = $this->getCacheKey($widgetInstance);

            $data = $this->cache->get($key, function (ItemInterface $item) use ($widgetInstance, $fields): array {
                if (null !== $widgetInstance->getCache()) {
                    $item->expiresAfter($widgetInstance->getCache());
                }

                return $this->getData($widgetInstance, $fields);
            });
        } else {
            $data = $this->getData($widgetInstance, $fields);
        }

        $widgetModel = new WidgetAsModel();
        $widgetModel->setFields($this->getFields($fields));
        $widgetModel->setData($data);
        $widgetModel->setEvents($widgetInstance->getEvents());
        $widgetModel->setProperties($widgetInstance->getProperties());
        $widgetModel->setParameters($this->getParameters($widgetInstance));
        $widgetModel->setFilters($this->getFilters($widgetInstance));
        $widgetModel->setPagination($this->getPagination($widgetInstance));

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
            throw new Exception(sprintf('Dashboard not found (%s)', $route->getCode()));
        }

        $route->setName($dashboard->getName());
        $route->setUrl($this->urlGenerator->generate('spyck_visualization_dashboard_show', [
            'dashboardId' => $dashboard->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL));

        $parameterFill = [];

        $fields = $route->getData();

        $parameters = $this->getDashboardParameterData($dashboard);

        $request = $this->requestStack->getCurrentRequest();

        foreach ($parameters as $parameter) {
            if ($parameter instanceof EntityParameterInterface) {
                $name = $parameter->getName();

                $parameterFill[$parameter->getField()] = array_key_exists($name, $fields) ? sprintf('{%s}', $fields[$name]) : $request->get($parameter->getField());
            }

            if ($parameter instanceof DateParameterInterface) {
                $parameterFill[$parameter->getField()] = $request->get($parameter->getField());
            }
        }

        $route->setParameters($parameterFill);
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

    private function getRequestData(RequestInterface $request, array $variables, bool $fill): ?string
    {
        $parameterBag = new ParameterBag();
        $parameterBag->add($variables);

        $field = $request->getField();

        if ($parameterBag->has($field)) {
            return sprintf('%s', $parameterBag->get($field));
        }

        if (false === $fill) {
            return null;
        }

        return match(get_class($request)) {
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

    private function getParameters(WidgetInterface $widget): array
    {
        $content = [];

        $parameters = $widget->getParameterData();

        foreach ($parameters as $parameter) {
            if ($parameter instanceof EntityParameterInterface) {
                $data = $parameter->getDataAsObject();

                if (null !== $data) {
                    $content[] = [
                        'name' => $parameter->getName(),
                        'data' => [
                            $data->getName(),
                        ],
                    ];
                }
            }
        }

        return $content;
    }

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
                            return $entity->getName();
                        }, $data),
                    ];
                }
            }

            if ($filter instanceof OptionFilter) { // OptionFilterInterface shows limit and offset
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
    private function getPagination(WidgetInterface $widget): ?Pagination
    {
        $pagination = $widget->getPagination();

        if (null === $pagination) {
            return null;
        }

        $name = 'spyck_visualization_widget_show';

        $parameters = $this->getPaginationParameters($name);

        if (null === $parameters) {
            return null;
        }

        $next = null;

        $data = $widget->getData();

        if (iterator_count($data) >= $pagination['limit']) {
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
            Field::TYPE_IMAGE => $this->getValueForImage($field, $value),
            Field::TYPE_TIME => $value instanceof DateTimeInterface ? $value : DateTimeUtility::getTimeFromString($value),
            default => $value,
        };
    }

    /**
     * @throws Exception
     */
    private function getValueForImage(Field $field, string $value): ?string
    {
        $class = $field->getConfig()->getClass();

        if (null === $class) {
            return null;
        }

        $image = $this->imageService->getImage($value, $field->getSource(), $class);

        if (null === $image) {
            return null;
        }

        return $this->imageService->getThumbnail($image, 'spyck_visualization');
    }

    private function getRoutes(Field $field, array $data = []): array
    {
        $content = [];

        foreach ($field->getRoutes() as $route) {
            $content[] = [
                'name' => $route->getName(),
                'url' => $this->getRouteUrl($route, $data),
            ];
        }

        return $content;
    }

    private function getRouteUrl(RouteInterface $route, array $data): string
    {
        $url = $route->getUrl();

        $parameters = array_filter($route->getParameters(), function (?string $value) {
            return null !== $value;
        });

        if (0 === count($parameters)) {
            return $url;
        }

        $search = [];
        $replace = [];

        foreach ($data as $key => $val) {
            $search[] = sprintf('{%s}', $key);
            $replace[] = (string) $val;
        }

        $query = [];

        foreach ($parameters as $name => $value) {
            $query[$name] = str_replace($search, $replace, $value);
        }

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
