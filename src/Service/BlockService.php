<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Exception;
use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Entity\Block as BlockAsEntity;
use Spyck\VisualizationBundle\Entity\Widget as WidgetAsEntity;
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
    public function getBlockAsModel(BlockAsEntity $blockAsEntity, array $variables = [], ?string $view = null, bool $preload = false): BlockAsModel
    {
        $blockAsModel = new BlockAsModel();

        $parameterBag = BlockUtility::getParameterBag($blockAsEntity, $variables);

        $widgetAsEntity = $blockAsEntity->getWidget();

        $widget = $this->widgetService->getWidget($widgetAsEntity->getAdapter(), $parameterBag->all());

        if ($preload) {
            $widget->setWidget($widgetAsEntity);
            $widget->setView($view);

            $blockAsModel->setWidget($this->widgetService->getWidgetAsModel($widget));
        }

        $blockAsModel->setName(null !== $blockAsEntity->getName() ? $blockAsEntity->getName() : $widgetAsEntity->getName());
        $blockAsModel->setDescription(null !== $blockAsEntity->getDescription() ? $blockAsEntity->getDescription() : $widgetAsEntity->getDescription());
        $blockAsModel->setDescriptionEmpty($widgetAsEntity->getDescriptionEmpty());
        $blockAsModel->setSize($blockAsEntity->getSize());
        $blockAsModel->setFilters($this->getBlockFilter($widget));
        $blockAsModel->setParameters($this->getBlockParameters($widget));
        $blockAsModel->setDownloads($this->getDownloads($blockAsEntity));
        $blockAsModel->setUrl($this->getBlockUrl($blockAsEntity, ViewInterface::JSON));
        $blockAsModel->setCharts($this->getCharts($blockAsEntity, $widgetAsEntity));
        $blockAsModel->setFilter($blockAsEntity->hasFilter());
        $blockAsModel->setFilterView($blockAsEntity->hasFilterView());

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
    private function getBlockParameters(WidgetInterface $widgetInstance): array
    {
        return array_merge($widgetInstance->getParameterDataRequest(), $widgetInstance->getFilterDataRequest());
    }

    private function getDownloads(BlockAsEntity $blockAsEntity): array
    {
        $data = [];

        foreach ($this->viewService->getViews() as $name => $view) {
            $data[] = [
                'name' => $this->translator->trans(id: sprintf('view.%s.name', $name), domain: 'SpyckVisualizationBundle'),
                'url' => $this->getBlockUrl($blockAsEntity, $name),
            ];
        }

        return $data;
    }
}
