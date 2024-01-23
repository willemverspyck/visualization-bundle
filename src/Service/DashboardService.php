<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
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
    public function __construct(private BlockService $blockService, private RouterInterface $router, private TranslatorInterface $translator, private UserService $userService, private WidgetService $widgetService, private ViewService $viewService)
    {
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getDashboardAsModel(Dashboard $dashboard, array $variables = [], string $view = null, bool $preload = false): DashboardAsModel
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

        $multipleParameters = new ArrayCollection();

        $parameters = $this->getDashboardParameterData($dashboard, $variables);

        array_walk ($parameters, function (ParameterInterface $parameter) use (&$data, &$multipleParameters, $slug): void {
            $parent = $parameter->getParent();

            if (null === $parent) {
                $name = get_class($parameter);

                $data[$name] = $parameter->getDataAsString($slug);

                return;
            }

            if (false === $multipleParameters->contains($parent)) {
                $multipleParameters->add($parent);
            }
        });

        foreach ($multipleParameters as $parameter) {
            $name = get_class($parameter);

            $data[$name] = $parameter->getDataAsString($slug);
        }

        return $data;
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

        foreach ($this->viewService->getViews() as $name => $view) {
            $data[] = [
                'id' => $name,
                'name' => $this->translator->trans(id: sprintf('view.%s.name', $name), domain: 'SpyckVisualizationBundle'),
            ];
        }

        return [
            'url' => $this->router->generate('spyck_visualization_dashboard_mail', [
                'dashboardId' => $dashboard->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'fields' => [
                'view' => [
                    'name' => 'Format',
                    'field' => 'view',
                    'data' => $data,
                ],
            ],
            'variables' => $variables,
        ];
    }
}
