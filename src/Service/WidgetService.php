<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use App\Utility\DataUtility;
use Countable;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use IteratorAggregate;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Spyck\ApiExtension\Model\Pagination;
use Spyck\VisualizationBundle\Callback\Callback;
use Spyck\VisualizationBundle\Entity\Block as BlockAsEntity;
use Spyck\VisualizationBundle\Entity\Dashboard as DashboardAsEntity;
use Spyck\VisualizationBundle\Entity\Widget as WidgetAsEntity;
use Spyck\VisualizationBundle\Exception\ParameterException;
use Spyck\VisualizationBundle\Field\AbstractFieldInterface;
use Spyck\VisualizationBundle\Field\Field;
use Spyck\VisualizationBundle\Field\FieldInterface;
use Spyck\VisualizationBundle\Field\MultipleFieldInterface;
use Spyck\VisualizationBundle\Filter\EntityFilterInterface;
use Spyck\VisualizationBundle\Filter\FilterInterface;
use Spyck\VisualizationBundle\Filter\LimitFilter;
use Spyck\VisualizationBundle\Filter\OffsetFilter;
use Spyck\VisualizationBundle\Filter\OptionFilterInterface;
use Spyck\VisualizationBundle\Model\Aggregate;
use Spyck\VisualizationBundle\Model\Block as BlockAsModel;
use Spyck\VisualizationBundle\Model\Dashboard as DashboardAsModel;
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
use Spyck\VisualizationBundle\Route\RouteForDashboard;
use Spyck\VisualizationBundle\Route\RouteInterface;
use Spyck\VisualizationBundle\Utility\BlockUtility;
use Spyck\VisualizationBundle\Utility\CacheUtility;
use Spyck\VisualizationBundle\Utility\DateTimeUtility;
use Spyck\VisualizationBundle\Utility\WidgetUtility;
use Spyck\VisualizationBundle\View\ViewInterface;
use Spyck\VisualizationBundle\Widget\WidgetInterface;
use Stringable;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
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
    public function __construct(#[Autowire(service: 'spyck.visualization.config.cache.adapter')] private CacheInterface $cache, private DashboardRepository $dashboardRepository, private readonly LoggerInterface $logger, private RepositoryService $repositoryService, private RequestStack $requestStack, private RouterInterface $router, private TranslatorInterface $translator, private UserService $userService, private UrlGeneratorInterface $urlGenerator, private WidgetRepository $widgetRepository, #[Autowire(param: 'spyck.visualization.config.cache.active')] private bool $cacheActive, #[Autowire(param: 'spyck.visualization.config.request')] private array $request, #[AutowireIterator(tag: 'spyck.visualization.widget')] private iterable $widgets)
    {
    }

    /**
     * Get instance of widget by name.
     *
     * @throws Exception
     * @throws ParameterException
     */
    public function getWidget(string $name, array $variables = [], bool $required = true): WidgetInterface
    {
        foreach ($this->widgets->getIterator() as $widget) {
            if (get_class($widget) === $name) {
                $this->setParameters($widget, $variables, $required);
                $this->setFilters($widget, $variables);

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
     * Get the data with a callback.
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getWidgetAsModel(WidgetInterface $widget): WidgetAsModel
    {
        $data = $this->getDataWithCache($widget);

        $fields = iterator_to_array($widget->getFields(), false);

        WidgetUtility::walkFields($fields, function (FieldInterface $field): void {
            $config = $field->getConfig();

            // Set defaults for abbreviation
            if (null === $config->hasAbbreviation() && in_array($field->getType(), [FieldInterface::TYPE_CURRENCY, FieldInterface::TYPE_NUMBER], true)) {
                $config->setAbbreviation(false);
            }

            match ($field->getType()) {
                FieldInterface::TYPE_CURRENCY, FieldInterface::TYPE_NUMBER => DataUtility::assert(null !== $config->hasAbbreviation(), new Exception(sprintf('Abbreviation must be NULL for "%s"', $field->getType()))),
                default => DataUtility::assert(null === $config->hasAbbreviation(), new Exception(sprintf('Abbreviation must not be NULL for "%s"', $field->getType()))),
            };

            // Set defaults for precision
            if (null === $config->getPrecision() && in_array($field->getType(), [FieldInterface::TYPE_CURRENCY, FieldInterface::TYPE_NUMBER, FieldInterface::TYPE_PERCENTAGE], true)) {
                $config->setPrecision(0);
            }

            match ($field->getType()) {
                FieldInterface::TYPE_CURRENCY, FieldInterface::TYPE_NUMBER, FieldInterface::TYPE_PERCENTAGE => DataUtility::assert(null !== $config->getPrecision(), new Exception(sprintf('Precision must be NULL for "%s"', $field->getType()))),
                default => DataUtility::assert(null === $config->getPrecision(), new Exception(sprintf('Precision must not be NULL for "%s"', $field->getType()))),
            };

            foreach ($field->getRoutes() as $route) {
                $this->setRoute($route);
            }
        }, false);

        $total = $widget->getTotal();
        $totalIncluded = null === $total;

        if (null === $total) {
            $total = count($data);
        }

        $widgetAsModel = new WidgetAsModel();
        $widgetAsModel->setFields($this->getFields($fields, $widget, $data));
        $widgetAsModel->setData($this->getData($data, $fields));
        $widgetAsModel->setTotal($total);
        $widgetAsModel->setEvents($widget->getEvents());
        $widgetAsModel->setProperties($widget->getProperties());
        $widgetAsModel->setParameters($this->getParameters($widget));
        $widgetAsModel->setFilters($this->getFilters($widget));
        $widgetAsModel->setPagination($this->getPagination($widget, $total, $totalIncluded));

        return $widgetAsModel;
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
    public function getDashboardParameterData(DashboardAsEntity $dashboardAsEntity, array $variables = []): array
    {
        $data = [];

        foreach ($dashboardAsEntity->getBlocks() as $block) {
            $parameterBag = BlockUtility::getParameterBag($block, $variables);

            $widgetInstance = $this->getWidget($block->getWidget()->getAdapter(), $parameterBag->all(), false);

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
    private function getWidgetDataByWidget(?WidgetAsEntity $widgetAsEntity, array $variables = []): DashboardAsModel
    {
        if (null === $widgetAsEntity) {
            throw new NotFoundHttpException('The widget does not exist');
        }

        $currentRequest = $this->requestStack->getCurrentRequest();

        $widget = $this->getWidget($widgetAsEntity->getAdapter(), $variables);
        $widget->setWidget($widgetAsEntity);
        $widget->setView(null === $currentRequest ? ViewInterface::JSON : $currentRequest->getRequestFormat());

        $blockAsModel = new BlockAsModel();
        $blockAsModel->setWidget($this->getWidgetAsModel($widget));
        $blockAsModel->setName($widgetAsEntity->getName());
        $blockAsModel->setDescriptionEmpty($widgetAsEntity->getDescriptionEmpty());
        $blockAsModel->setCharts($widgetAsEntity->getCharts());

        $user = $this->userService->getUser();

        $dashboardAsModel = new DashboardAsModel();
        $dashboardAsModel->setUser($user);
        $dashboardAsModel->setName($widgetAsEntity->getName());
        $dashboardAsModel->addBlock($blockAsModel);

        return $dashboardAsModel;
    }

    /**
     * Set the parameters of a widget.
     *
     * @throws Exception
     * @throws ParameterException
     */
    private function setParameters(WidgetInterface $widget, array $variables, bool $required = true): void
    {
        $parameters = $this->mapRequest($widget->getParameters(), function (ParameterInterface $parameter) use ($variables, $required): void {
            $data = $this->getRequestData($parameter, $variables);

            if (null === $data) {
                if ($required) {
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
    private function setFilters(WidgetInterface $widget, array $variables): void
    {
        $filters = $this->mapRequest($widget->getFilters(), function (FilterInterface $filter) use ($variables): void {
            $data = $this->getRequestData($filter, $variables);

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
    private function getRequestData(RequestInterface $request, array $variables): ?string
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

    private function getFields(array $fields, WidgetInterface $widget, array $data): array
    {
        WidgetUtility::walkFields($fields, function (FieldInterface $field) use ($widget, $data): void {
            $field->setAggregate($this->getAggregate($field, $data));

            $filter = $field->getFilter();

            if (null === $filter) {
                return;
            }

            $active = call_user_func($filter->getName(), $widget, $data, $filter->getParameters());

            $field->setActive($active);
        }, false);

        WidgetUtility::walkMultipleFields($fields, function (MultipleFieldInterface $field) use ($data): void {
            $field->setAggregate($this->getAggregate($field, $data));

            $children = $field->getChildren()->filter(function (FieldInterface $field): bool {
                return $field->isActive();
            });

            $active = false === $children->isEmpty();

            $field->setActive($active);
        }, false);

        return $fields;
    }

    /**
     * @throws Exception
     */
    private function getData(array $data, array $fields): array
    {
        $content = [];

        foreach ($data as $row) {
            $content[] = WidgetUtility::mapFields($fields, function (Field $field) use ($row): array {
                return [
                    'value' => $this->getValue($field, $row),
                    'routes' => $this->getRoutes($field, $row),
                ];
            });
        }

        return $content;
    }

    private function getDataWithCache(WidgetInterface $widget): array
    {
        if (false === $this->cacheActive || null === $widget->getCache()) {
            $this->logger->info('Cache disabled');

            return iterator_to_array($widget->getData(), false);
        }

        $key = $this->getKeyForCache($widget);

        $data = $this->cache->get($key, function (ItemInterface $item) use ($widget): array {
            $item->expiresAfter($widget->getCache());

            if ($this->cache instanceof TagAwareCacheInterface) {
                $item->tag(sprintf('spyck_visualization_widget_%s', $widget->getWidget()->getId()));
            }

            return iterator_to_array($widget->getData(), false);
        }, null, $metadata);

        $this->logger->info('Cache', [
            'cache' => $widget->getCache(),
            'metadata' => $metadata,
        ]);

        return $data;
    }

    private function getAggregate(AbstractFieldInterface $field, array $data): Aggregate
    {
        if ($field instanceof MultipleFieldInterface) {
            $fields = $field->getChildren();
        } else {
            $fields = new ArrayCollection();
            $fields->add($field);
        }

        $types = [];
        $values = [];

        foreach ($fields as $field) {
            $type = $field->getType();

            if (false === in_array($type, $types, true)) {
                $types[] = $field->getType();
            }

            foreach ($data as $row) {
                $value = $this->getValue($field, $row);

                if (null !== $value) {
                    $values[] = $value;
                }
            }
        }

        $aggregate = new Aggregate();

        if (1 !== count($types)) {
            return $aggregate;
        }

        if (0 === count($values)) {
            return $aggregate;
        }

        $type = array_shift($types);

        if (in_array($type, [FieldInterface::TYPE_ARRAY, FieldInterface::TYPE_BOOLEAN, FieldInterface::TYPE_IMAGE, FieldInterface::TYPE_TEXT], true)) {
            return $aggregate;
        }

        $aggregate->setMin(min($values));
        $aggregate->setMax(max($values));
        $aggregate->setMedian($this->getMedian($type, $values));

        return $aggregate;
    }

    private function getMedian(string $type, array $data): DateTimeInterface|float|int|null
    {
        $count = count($data);

        if (0 === $count) {
            return null;
        }

        sort($data);

        $index = floor($count / 2);

        if (0 === $count % 2) {
            $value1 = $data[$index - 1];
            $value2 = $data[$index];

            if (in_array($type, [FieldInterface::TYPE_DATE, FieldInterface::TYPE_DATETIME, FieldInterface::TYPE_TIME], true)) {
                $timestamp = ($value1->getTimestamp() + $value2->getTimestamp()) / 2;

                $date = new DateTime();
                $date->setTimestamp((int) $timestamp);

                return $date;
            }

            return ($value1 + $value2) / 2;
        }

        return $data[$index];
    }

    /**
     * Get the data with a callback.
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    private function getKeyForCache(WidgetInterface $widgetInstance): string
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
            FieldInterface::TYPE_CURRENCY, FieldInterface::TYPE_NUMBER, FieldInterface::TYPE_PERCENTAGE => (float) $value,
            FieldInterface::TYPE_DATE => $value instanceof DateTimeInterface ? $value : DateTimeUtility::getDateFromString($value),
            FieldInterface::TYPE_DATETIME => $value instanceof DateTimeInterface ? $value : DateTimeUtility::getDateTimeFromString($value),
            FieldInterface::TYPE_TIME => $value instanceof DateTimeInterface ? $value : DateTimeUtility::getTimeFromString($value),
            default => $value,
        };
    }

    private function getRoutes(FieldInterface $field, array $data = []): array
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
}
