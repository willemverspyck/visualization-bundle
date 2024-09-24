<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Exception;
use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Entity\Block;
use Spyck\VisualizationBundle\Entity\Widget;
use Spyck\VisualizationBundle\Filter\EntityFilterInterface;
use Spyck\VisualizationBundle\Filter\FilterInterface;
use Spyck\VisualizationBundle\Filter\OptionFilterInterface;
use Spyck\VisualizationBundle\Model\Block as BlockAsModel;
use Spyck\VisualizationBundle\Model\Filter as FilterAsModel;
use Spyck\VisualizationBundle\Utility\BlockUtility;
use Spyck\VisualizationBundle\View\ViewInterface;
use Spyck\VisualizationBundle\Widget\WidgetInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class BlockService
{
    public function __construct(private RouterInterface $router, private RepositoryService $repositoryService, private TranslatorInterface $translator, private WidgetService $widgetService, private ViewService $viewService)
    {
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getBlockAsModel(Block $block, array $variables = [], ?string $view = null, bool $preload = false): BlockAsModel
    {
        $blockAsModel = new BlockAsModel();

        $parameterBag = BlockUtility::getParameterBag($block, $variables);

        $widget = $block->getWidget();

        $widgetInstance = $this->widgetService->getWidget($widget->getAdapter(), $parameterBag->all());

        if ($preload) {
            $widgetInstance->setWidget($widget);
            $widgetInstance->setView($view);

            $blockAsModel->setWidget($this->widgetService->getWidgetAsModel($widgetInstance));
        }

        $blockAsModel->setName(null !== $block->getName() ? $block->getName() : $widget->getName());
        $blockAsModel->setDescription(null !== $block->getDescription() ? $block->getDescription() : $widget->getDescription());
        $blockAsModel->setDescriptionEmpty($widget->getDescriptionEmpty());
        $blockAsModel->setSize($block->getSize());
        $blockAsModel->setFilters($this->getBlockFilter($widgetInstance));
        $blockAsModel->setParameters($this->getBlockParameters($widgetInstance));
        $blockAsModel->setDownloads($this->getDownloads($block));
        $blockAsModel->setUrl($this->getBlockUrl($block, ViewInterface::JSON));
        $blockAsModel->setCharts($this->getCharts($block, $widget));
        $blockAsModel->setFilter($block->hasFilter());
        $blockAsModel->setFilterView($block->hasFilterView());

        return $blockAsModel;
    }

    private function getCharts(Block $block, Widget $widget): array
    {
        if (null === $block->getChart()) {
            return $widget->getCharts();
        }

        if (in_array($block->getChart(), $widget->getCharts(), true)) {
            return array_values(array_unique(array_merge([$block->getChart()], $widget->getCharts())));
        }

        return $widget->getCharts();
    }

    /**
     * Get filters of the widget.
     */
    private function getBlockFilter(WidgetInterface $widget): array
    {
        $data = [];

        foreach ($widget->getFilterData() as $filter) {
            $name = $filter->getName();

            $filterAsModel = new FilterAsModel();
            $filterAsModel->setName($this->translator->trans(id: sprintf('filter.%s.description', $name), domain: 'SpyckVisualizationBundle'));
            $filterAsModel->setField($filter->getField());
            $filterAsModel->setConfig($filter->getConfig());
            $filterAsModel->setData($filter->getData());
            $filterAsModel->setOptions($this->getFilterOptions($filter, $widget));
            $filterAsModel->setType($filter->getType());

            $data[] = $filterAsModel;
        }

        return $data;
    }

    private function getFilterOptions(FilterInterface $filter, WidgetInterface $widget): array
    {
        $data = [];

        if (false === $filter->preload()) {
            return $data;
        }

        if ($filter instanceof EntityFilterInterface) {
            $items = $filter->getDataAsObject();

            if (null === $items) {
                $items = [];
            }

            $items = array_map(function (object $object): int {
                return $object->getId();
            }, $items);

            $name = $filter->getName();

            $entityData = $this->repositoryService->getRepository($name)->getVisualizationEntityData($widget);

            foreach ($entityData as $entityRow) {
                $data[] = [
                    'id' => $entityRow->getId(),
                    'parent' => null,
                    'name' => $entityRow->getName(),
                    'select' => in_array($entityRow->getId(), $items, true),
                ];
            }
        }

        if ($filter instanceof OptionFilterInterface) {
            $items = $filter->getData();

            if (null === $items) {
                $items = [];
            }

            foreach ($filter->getOptions() as $optionId => $optionName) {
                $data[] = [
                    'id' => $optionId,
                    'parent' => null,
                    'name' => $optionName,
                    'select' => in_array($optionId, $items, true),
                ];
            }
        }

        return $data;
    }

    /**
     * Get url of the widget.
     */
    private function getBlockUrl(Block $block, string $format): string
    {
        $widget = $block->getWidget();

        $parameters = [
            'widgetId' => $widget->getId(),
            '_format' => $format,
        ];

        return $this->router->generate('spyck_visualization_widget_item', $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Get url of the widget.
     */
    private function getBlockParameters(WidgetInterface $widgetInstance): array
    {
        return array_merge($widgetInstance->getParameterDataRequest(), $widgetInstance->getFilterDataRequest());
    }

    private function getDownloads(Block $block): array
    {
        $data = [];

        foreach ($this->viewService->getViews() as $name => $view) {
            $data[] = [
                'name' => $this->translator->trans(id: sprintf('view.%s.name', $name), domain: 'SpyckVisualizationBundle'),
                'url' => $this->getBlockUrl($block, $name),
            ];
        }

        return $data;
    }
}
