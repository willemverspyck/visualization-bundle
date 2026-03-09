<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Exception;
use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Entity\Block as BlockAsEntity;
use Spyck\VisualizationBundle\Entity\Widget as WidgetAsEntity;
use Spyck\VisualizationBundle\Event\BlockEvent;
use Spyck\VisualizationBundle\Event\FilterEvent;
use Spyck\VisualizationBundle\Model\Block as BlockAsModel;
use Spyck\VisualizationBundle\Model\Filter as FilterAsModel;
use Spyck\VisualizationBundle\Model\Parameter as ParameterAsModel;
use Spyck\VisualizationBundle\View\ViewInterface;
use Spyck\VisualizationBundle\Widget\WidgetInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class BlockService
{
    public function __construct(private EventDispatcherInterface $eventDispatcher, private RouterInterface $router, private RepositoryService $repositoryService, private TranslatorInterface $translator, private WidgetService $widgetService, private ViewService $viewService)
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

    private function getCharts(BlockAsEntity $blockAsEntity, WidgetAsEntity $widget): array
    {
        if (null === $blockAsEntity->getChart()) {
            return $widget->getCharts();
        }

        if (in_array($blockAsEntity->getChart(), $widget->getCharts(), true)) {
            return array_values(array_unique(array_merge([$blockAsEntity->getChart()], $widget->getCharts())));
        }

        return $widget->getCharts();
    }

    /**
     * Get filters of the widget.
     */
    private function getBlockFilters(WidgetInterface $widget): array
    {
        $data = [];

        foreach ($widget->getFilterData() as $filter) {
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

            $data[] = $filterAsModel;
        }

        return $data;
    }

    /**
     * Get filters of the widget.
     */
    private function getBlockParameters(WidgetInterface $widget): array
    {
        $data = [];

        foreach ($widget->getParameterData() as $parameter) {
            $parameterAsModel = new ParameterAsModel();
            $parameterAsModel->setName($this->translator->trans(id: sprintf('parameter.%s.description', $parameter::getName()), domain: 'SpyckVisualizationBundle'));
            $parameterAsModel->setField($parameter::getField());

            $data[] = $parameterAsModel;
        }

        return $data;
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

    private function getDownloads(BlockAsEntity $blockAsEntity): array
    {
        $data = [];

        foreach ($this->viewService->getViews() as $name => $view) {
            $data[] = [
                'id' => $name,
                'name' => $this->translator->trans(id: sprintf('view.%s.name', $name), domain: 'SpyckVisualizationBundle'),
                'url' => $this->getBlockUrl($blockAsEntity, $name),
            ];
        }

        return $data;
    }
}
