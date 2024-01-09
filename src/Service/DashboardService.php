<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Entity\Mail;
use Spyck\VisualizationBundle\Model\Dashboard as DashboardAsModel;
use Spyck\VisualizationBundle\Model\DashboardRoute;
use Spyck\VisualizationBundle\Utility\BlockUtility;
use Spyck\VisualizationBundle\View\ViewInterface;
use Spyck\VisualizationBundle\Request\RequestInterface;
use Spyck\VisualizationBundle\Parameter\DateParameterInterface;
use Spyck\VisualizationBundle\Parameter\DayRangeParameter;
use Spyck\VisualizationBundle\Parameter\EntityParameterInterface;
use Spyck\VisualizationBundle\Parameter\MonthRangeParameter;
use Spyck\VisualizationBundle\Parameter\ParameterInterface;
use Spyck\VisualizationBundle\Parameter\WeekRangeParameter;
use Exception;
use ReflectionClass;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class DashboardService
{
    public function __construct(private BlockService $blockService, private RouterInterface $router, private TranslatorInterface $translator, private UserService $userService, private WidgetService $widgetService)
    {
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getDashboardAsModel(Dashboard $dashboard, array $variables = [], string $view = ViewInterface::JSON, bool $preload = false): DashboardAsModel
    {
        $user = $this->userService->getUser();

        $variables = $this->filterVariables($dashboard, $variables);

        $dashboardRoute = $this->getDashboardRoute($dashboard, $variables);

        $dashboardModel = new DashboardAsModel();

        $dashboardModel
            ->setUser($user)
            ->setName($dashboard->getName())
            ->setDescription($dashboard->getDescription())
            ->setUrl($dashboardRoute->getUrl())
            ->setParameters($this->getDashboardParameters($dashboard, $variables))
            ->setParametersAsString($this->getDashboardParametersAsString($dashboard, $variables))
            ->setParametersAsStringForSlug($this->getDashboardParametersAsString($dashboard, $variables, true))
            ->setDownloads($this->getDownloads($dashboard, $variables));

        foreach ($dashboard->getBlocks() as $block) {
            $blockAsModel = $this->blockService->getBlockAsModel($block, $variables, $view, $preload);

            $dashboardModel->addBlock($blockAsModel);
        }

        return $dashboardModel;
    }

    /**
     * Get required parameters for dashboard.
     *
     * @return array<int, ParameterInterface>
     *
     * @throws Exception
     */
    public function getDashboardParameterData(Dashboard $dashboard, array $variables = []): array
    {
        $data = [];

        foreach ($dashboard->getBlocks() as $block) {
            $parameterBag = BlockUtility::getParameterBag($block, $variables);

            $widgetInstance = $this->widgetService->getWidgetInstance($block->getWidget()->getAdapter(), $parameterBag->all(), true);

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
     * @return array<string, string>
     *
     * @throws Exception
     */
    public function getDashboardParameters(Dashboard $dashboard, array $variables): array
    {
        $data = [];

        foreach ($this->getDashboardParameterData($dashboard, $variables) as $parameter) {
            $reflectionClass = new ReflectionClass($parameter);

            $name = $reflectionClass->getShortName();

            if ($parameter instanceof DateParameterInterface) {
                $data[$name] = $parameter->getDataForRequest();
            }

            if ($parameter instanceof EntityParameterInterface) {
                if ($parameter->isRequest()) {
                    $data[$name] = $parameter;
                }
            }
        }

        return $data;
    }

    /**
     * @return array<string, string>
     *
     * @throws Exception
     */
    public function getDashboardParametersAsString(Dashboard $dashboard, array $variables, bool $slug = false): array
    {
        $data = [];

        $childrenExclude = [];

        $hasMultipleRequest = false;

        $parameters = $this->getDashboardParameterData($dashboard, $variables);

        foreach ([new DayRangeParameter(), new MonthRangeParameter(), new WeekRangeParameter()] as $multipleRequest) {
            $children = $multipleRequest->getChildren();

            $intersect = array_uintersect($parameters, $children, function (RequestInterface $a, RequestInterface $b): int {
                if (get_class($a) === get_class($b)) {
                    return 0;
                }

                return get_class($a) > get_class($b) ? 1 : -1;
            });

            if (count($intersect) === count($children)) {
                $childrenExclude = array_merge($childrenExclude, $children);

                if (false === $hasMultipleRequest) {
                    $range = [];

                    foreach ($intersect as $intersects) {
                        $range[] = $intersects->getDataAsString($slug);
                    }

                    $reflectionClass = new ReflectionClass($multipleRequest);

                    $name = $reflectionClass->getShortName();

                    $data[$name] = implode(' - ', $range);

                    $hasMultipleRequest = true;
                }
            }
        }

        $difference = array_udiff($parameters, $childrenExclude, function (RequestInterface $a, RequestInterface $b): int {
            if (get_class($a) === get_class($b)) {
                return 0;
            }

            return get_class($a) > get_class($b) ? 1 : -1;
        });

        foreach ($difference as $name => $parameter) {
            $data[$name] = $parameter->getDataAsString($slug);
        }

        /** If there is a dateRange object, put it at the end of the array */
        if ($hasMultipleRequest) {
            $data = array_slice($data, 1) + array_slice($data, 0, 1);
        }

        return array_filter($data);
    }

    /**
     * Check for missing dashboard parameters.
     *
     * @throws Exception
     */
    public function checkDashboardParameterData(Dashboard $dashboard, array $variables = []): ?array
    {
        $returnData = [];

        $parameters = $this->getDashboardParameterData($dashboard, $variables);

        foreach ($parameters as $parameter) {
            if ($parameter instanceof EntityParameterInterface) {
                $data = $parameter->getData();

                if (null === $data) {
                    $returnData[] = [
                        'url' => $this->router->generate($parameter->getRoute(), [], UrlGeneratorInterface::ABSOLUTE_URL),
                        'variables' => $variables,
                        'field' => $parameter->getField(),
                    ];
                }
            }
        }

        if (0 === count($returnData)) {
            return null;
        }

        return $returnData;
    }

    public function getDashboardRoute(Dashboard $dashboard, array $variables = []): DashboardRoute
    {
        $variables['dashboardId'] = $dashboard->getId();

        $dashboardRequest = new DashboardRoute();

        return $dashboardRequest
            ->setName($dashboard->getName())
            ->setUrl($this->router->generate('spyck_visualization_dashboard_show', $variables, UrlGeneratorInterface::ABSOLUTE_URL));
    }

    /**
     * Remove variables that are not part of the dashboard.
     *
     * @throws Exception
     */
    private function filterVariables(Dashboard $dashboard, array $variables = []): array
    {
        $data = [];

        foreach ($dashboard->getBlocks() as $block) {
            $parameterBag = BlockUtility::getParameterBag($block, $variables);

            $widgetInstance = $this->widgetService->getWidgetInstance($block->getWidget()->getAdapter(), $parameterBag->all(), true);

            $data = array_replace($data, $widgetInstance->getParameterDataRequest(), $widgetInstance->getFilterDataRequest());
        }

        return array_intersect_key($variables, $data);
    }

    private function getDownloads(Dashboard $dashboard, array $variables): array
    {
        $data = [];

        $formats = Mail::getViews();

        foreach ($formats as $format) {
            $data[] = [
                'id' => $format,
                'name' => $this->translator->trans(id: sprintf('download.%s', $format), domain: 'SpyckVisualizationBundle'),
            ];
        }

        return [
            'url' => $this->router->generate('spyck_visualization_dashboard_mail', [
                'dashboardId' => $dashboard->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'fields' => [
                'view' => [
                    'data' => $data,
                    'name' => 'Format',
                    'parameter' => 'view',
                    'type' => 'radio',
                ],
            ],
            'parameters' => [
                'variables' => $variables,
            ],
        ];
    }
}
