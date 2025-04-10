<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Psr\Cache\InvalidArgumentException;
use ReflectionClass;
use Spyck\VisualizationBundle\Entity\Dashboard as DashboardAsEntity;
use Spyck\VisualizationBundle\Model\Dashboard as DashboardAsModel;
use Spyck\VisualizationBundle\Model\Route as RouteAsModel;
use Spyck\VisualizationBundle\Parameter\DateParameterInterface;
use Spyck\VisualizationBundle\Parameter\EntityParameterInterface;
use Spyck\VisualizationBundle\Parameter\ParameterInterface;
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
    public function getDashboardAsModel(DashboardAsEntity $dashboardAsEntity, array $variables = [], ?string $view = null, bool $preload = false): DashboardAsModel
    {
        $user = $this->userService->getUserAsString();

        $variables = $this->validateVariables($dashboardAsEntity, $variables);

        $dashboardRoute = $this->getRoute($dashboardAsEntity, $variables);

        $dashboardAsModel = new DashboardAsModel();

        $dashboardAsModel
            ->setId($dashboardAsEntity->getId())
            ->setUser($user)
            ->setName($dashboardAsEntity->getName())
            ->setDescription($dashboardAsEntity->getDescription())
            ->setUrl($dashboardRoute->getUrl())
            ->setParameters($this->getParameters($dashboardAsEntity, $variables))
            ->setParametersAsString($this->getParametersAsString($dashboardAsEntity, $variables))
            ->setParametersAsStringForSlug($this->getParametersAsString($dashboardAsEntity, $variables, true))
            ->setDownloads($this->getDownloads($dashboardAsEntity, $variables))
            ->setVariables($this->getVariables($dashboardAsEntity, $variables));

        foreach ($dashboardAsEntity->getBlocks() as $block) {
            $blockAsModel = $this->blockService->getBlockAsModel($block, $variables, $view, $preload);

            $dashboardAsModel->addBlock($blockAsModel);
        }

        return $dashboardAsModel;
    }

    /**
     * @return array<string, string>
     *
     * @throws Exception
     */
    public function getParameters(DashboardAsEntity $dashboardAsEntity, array $variables, bool $field = false): array
    {
        $data = [];

        foreach ($this->widgetService->getParametersByDashboard($dashboardAsEntity, $variables, true) as $parameter) {
            if ($field) {
                $name = $parameter::getField();
            } else {
                $reflectionClass = new ReflectionClass($parameter);

                $name = $reflectionClass->getShortName();
            }

            if ($parameter instanceof DateParameterInterface) {
                $data[$name] = $parameter->getDataForRequest();
            }

            if ($parameter instanceof EntityParameterInterface) {
                $data[$name] = $parameter;
            }
        }

        return $data;
    }

    /**
     * @return array<string, string>
     *
     * @throws Exception
     */
    public function getParametersAsString(DashboardAsEntity $dashboardAsEntity, array $variables, bool $slug = false): array
    {
        $data = [];

        $multipleParameters = new ArrayCollection();

        $parameters = $this->widgetService->getParametersByDashboard($dashboardAsEntity, $variables, true);

        array_walk($parameters, function (ParameterInterface $parameter) use (&$data, &$multipleParameters, $slug): void {
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
    public function validateParameters(DashboardAsEntity $dashboardAsEntity, array $variables = []): ?array
    {
        $returnData = [];

        $parameters = $this->widgetService->getParametersByDashboard($dashboardAsEntity, $variables);

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

    private function getDownloads(DashboardAsEntity $dashboardAsEntity, array $variables): array
    {
        $data = [];

        foreach ($this->viewService->getViews() as $name => $view) {
            $data[] = [
                'id' => $name,
                'name' => $this->translator->trans(id: sprintf('view.%s.name', $name), domain: 'SpyckVisualizationBundle'),
            ];
        }

        return [
            'url' => $this->router->generate('spyck_visualization_mail_dashboard', [
                'dashboardId' => $dashboardAsEntity->getId(),
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

    public function getRoute(DashboardAsEntity $dashboardAsEntity, array $variables = []): RouteAsModel
    {
        $variables['dashboardId'] = $dashboardAsEntity->getId();

        $routeAsModel = new RouteAsModel();

        return $routeAsModel
            ->setName($dashboardAsEntity->getName())
            ->setUrl($this->router->generate('spyck_visualization_dashboard_show', $variables, UrlGeneratorInterface::ABSOLUTE_URL));
    }

    private function getVariables(DashboardAsEntity $dashboardAsEntity, array $variables): array
    {
        $data = $this->getParameters($dashboardAsEntity, $variables, true);

        return array_merge($data, $variables);
    }

    /**
     * Remove variables that are not part of the dashboard.
     *
     * @throws Exception
     */
    private function validateVariables(DashboardAsEntity $dashboardAsEntity, array $variables = []): array
    {
        $data = [];

        foreach ($dashboardAsEntity->getBlocks() as $block) {
            $widget = $this->widgetService->getWidgetByBlock($block, $variables);

            $data = array_replace($data, $widget->getParameterDataRequest(), $widget->getFilterDataRequest());
        }

        return array_intersect_key($variables, $data);
    }
}
