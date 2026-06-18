<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Exception;
use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Chart\ChartInterface;
use Spyck\VisualizationBundle\Entity\Block as BlockAsEntity;
use Spyck\VisualizationBundle\Entity\Widget as WidgetAsEntity;
use Spyck\VisualizationBundle\Event\BlockEvent;
use Spyck\VisualizationBundle\Event\FilterEvent;
use Spyck\VisualizationBundle\Filter\FilterInterface;
use Spyck\VisualizationBundle\Model\Block as BlockAsModel;
use Spyck\VisualizationBundle\Model\Chart as ChartAsModel;
use Spyck\VisualizationBundle\Model\Download as DownloadAsModel;
use Spyck\VisualizationBundle\Model\Filter as FilterAsModel;
use Spyck\VisualizationBundle\Model\Parameter as ParameterAsModel;
use Spyck\VisualizationBundle\Parameter\ParameterInterface;
use Spyck\VisualizationBundle\View\ViewInterface;
use Spyck\VisualizationBundle\Widget\WidgetInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class BlockService
{
    public function __construct(private ChartService $chartService, private EventDispatcherInterface $eventDispatcher, private RouterInterface $router, private RepositoryService $repositoryService, private TranslatorInterface $translator, private WidgetService $widgetService, private ViewService $viewService)
    {
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getBlockAsModel(BlockAsEntity $blockAsEntity, array $variables = [], ?string $view = null, bool $preload = false): BlockAsModel
    {
        $widget = $this->widgetService->getWidgetByBlock($blockAsEntity, $variables);
        $widgetAsEntity = $blockAsEntity->getWidget();

        $blockAsModel = new BlockAsModel();

        if ($preload) {
            $widget->setWidget($widgetAsEntity);
            $widget->setView($view);

            $blockAsModel->setWidget($this->widgetService->getWidgetAsModel($widget));
        }

        $blockAsModel->setName(null !== $blockAsEntity->getName() ? $blockAsEntity->getName() : $widgetAsEntity->getName());
        $blockAsModel->setDescription(null !== $blockAsEntity->getDescription() ? $blockAsEntity->getDescription() : $widgetAsEntity->getDescription());
        $blockAsModel->setDescriptionEmpty($widgetAsEntity->getDescriptionEmpty());
        $blockAsModel->setSize($blockAsEntity->getSize());
        $blockAsModel->setFilters($this->getBlockFilters($widget));
        $blockAsModel->setParameters($this->getBlockParameters($widget));
        $blockAsModel->setVariables($this->getBlockVariables($widget));
        $blockAsModel->setDownloads($this->getDownloads($blockAsEntity));
        $blockAsModel->setUrl($this->getBlockUrl($blockAsEntity, ViewInterface::JSON));
        $blockAsModel->setCharts($this->getCharts($blockAsEntity, $widgetAsEntity));
        $blockAsModel->setFilter($blockAsEntity->hasFilter());
        $blockAsModel->setFilterView($blockAsEntity->hasFilterView());
        $blockAsModel->setLazy($blockAsEntity->isLazy());

        $blockEvent = new BlockEvent($blockAsModel);

        $this->eventDispatcher->dispatch($blockEvent);

        return $blockAsModel;
    }

    private function getCharts(BlockAsEntity $blockAsEntity, WidgetAsEntity $widgetAsEntity): array
    {
        $chartCode = $blockAsEntity->getChart();
        $chartCodes = $widgetAsEntity->getCharts();

        if (null !== $chartCode) {
            $chartCodes = [$chartCode, ...array_diff($chartCodes, [$chartCode])];
        }

        $data = [];

        $charts = $this->chartService->getCharts();

        foreach ($chartCodes as $chartCode) {
            foreach ($charts as $chart) {
                if ($chartCode === $chart->getCode()) {
                    $data[] = $chart;
                }
            }
        }

        return array_map(function (ChartInterface $chart): ChartAsModel {
            $code = $chart->getCode();

            $chartAsModel = new ChartAsModel();
            $chartAsModel->setCode($code);
            $chartAsModel->setName($this->translator->trans(id: sprintf('chart.%s.name', $code), domain: 'SpyckVisualizationBundle'));

            return $chartAsModel;
        }, $data);
    }

    /**
     * Get filters of the widget.
     */
    private function getBlockFilters(WidgetInterface $widget): array
    {
        return array_map(function (FilterInterface $filter) use ($widget): FilterAsModel {
            $name = $filter->getName();

            $options = [];

            if ($filter->preload()) {
                $filterEvent = new FilterEvent($filter, $widget);
                $filterEvent = $this->eventDispatcher->dispatch($filterEvent);

                $options = $filterEvent->getOptions();
            }

            $filterAsModel = new FilterAsModel();
            $filterAsModel->setName($this->translator->trans(id: sprintf('filter.%s.description', $name), domain: 'SpyckVisualizationBundle'));
            $filterAsModel->setField($filter->getField());
            $filterAsModel->setConfig($filter->getConfig());
            $filterAsModel->setData($filter->getData());
            $filterAsModel->setOptions($options);
            $filterAsModel->setType($filter->getType());

            return $filterAsModel;
        }, array_values($widget->getFilterData()));
    }

    /**
     * Get filters of the widget.
     */
    private function getBlockParameters(WidgetInterface $widget): array
    {
        return array_map(function (ParameterInterface $parameter): ParameterAsModel {
            $parameterAsModel = new ParameterAsModel();
            $parameterAsModel->setName($this->translator->trans(id: sprintf('parameter.%s.name', $parameter->getName()), domain: 'SpyckVisualizationBundle'));
            $parameterAsModel->setField($parameter->getField());

            return $parameterAsModel;
        }, array_values($widget->getParameterData()));
    }

    /**
     * Get url of the widget.
     */
    private function getBlockUrl(BlockAsEntity $blockAsEntity, string $format): string
    {
        $widget = $blockAsEntity->getWidget();

        $parameters = [
            'widgetId' => $widget->getId(),
            '_format' => $format,
        ];

        return $this->router->generate('spyck_visualization_widget_item', $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Get url of the widget.
     */
    private function getBlockVariables(WidgetInterface $widget): array
    {
        return array_merge($widget->getParameterDataRequest(), $widget->getFilterDataRequest());
    }

    /**
     * @return list<DownloadAsModel>
     */
    private function getDownloads(BlockAsEntity $blockAsEntity): array
    {
        return array_map(function (ViewInterface $view) use ($blockAsEntity): DownloadAsModel {
            $code = $view->getCode();

            $downloadAsModel = new DownloadAsModel();
            $downloadAsModel->setCode($code);
            $downloadAsModel->setName($this->translator->trans(id: sprintf('view.%s.name', $code), domain: 'SpyckVisualizationBundle'));
            $downloadAsModel->setUrl($this->getBlockUrl($blockAsEntity, $code));

            return $downloadAsModel;
        }, $this->viewService->getViews());
    }
}
