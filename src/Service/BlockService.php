<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Entity\Block;
use Spyck\VisualizationBundle\Entity\Mail;
use Spyck\VisualizationBundle\Entity\Widget;
use Spyck\VisualizationBundle\Model\Block as BlockAsModel;
use Spyck\VisualizationBundle\Model\Filter;
use Spyck\VisualizationBundle\Utility\BlockUtility;
use Spyck\VisualizationBundle\View\ViewInterface;
use Spyck\VisualizationBundle\Widget\WidgetInterface;
use Spyck\VisualizationBundle\Filter\EntityFilterInterface;
use Spyck\VisualizationBundle\Filter\OptionFilterInterface;
use Exception;
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
    public function getBlockAsModel(Block $block, array $variables = [], string $view = null, bool $preload = false): BlockAsModel
    {
        $blockModel = new BlockAsModel();

        $parameterBag = BlockUtility::getParameterBag($block, $variables);

        if ($preload) {
            $blockModel->setWidget($this->widgetService->getWidgetAsModel($block, $parameterBag->all(), $view));
        }

        $widget = $block->getWidget();

        $widgetInstance = $this->widgetService->getWidgetInstance($widget->getAdapter(), $parameterBag->all(), true);

        $blockModel->setName(null !== $block->getName() ? $block->getName() : $widget->getName());
        $blockModel->setDescription(null !== $block->getDescription() ? $block->getDescription() : $widget->getDescription());
        $blockModel->setDescriptionEmpty($widget->getDescriptionEmpty());
        $blockModel->setSize($block->getSize());
        $blockModel->setFilters($this->getBlockFilter($widgetInstance));
        $blockModel->setParameters($this->getBlockParameters($widgetInstance));
        $blockModel->setDownloads($this->getDownloads($block));
        $blockModel->setUrl($this->getBlockUrl($block, ViewInterface::JSON));
        $blockModel->setCharts($this->getCharts($block, $widget));
        $blockModel->setFilter($block->hasFilter());
        $blockModel->setFilterView($block->hasFilterView());

        return $blockModel;
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
    private function getBlockFilter(WidgetInterface $widgetInstance): array
    {
        $data = [];

        foreach ($widgetInstance->getFilterData() as $filter) {
            $name = $filter->getName();

            $returnEntityData = [];

            if ($filter instanceof EntityFilterInterface) {
                $items = $filter->getDataAsObject();

                if (null === $items) {
                    $items = [];
                }

                $items = array_map(function (object $object): int {
                    return $object->getId();
                }, $items);

                $entityData = $this->repositoryService->getRepository($name)->getVisualizationEntityData($widgetInstance);

                foreach ($entityData as $entityRow) {
                    $returnEntityData[] = [
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
                    $returnEntityData[] = [
                        'id' => $optionId,
                        'parent' => null,
                        'name' => $optionName,
                        'select' => in_array($optionId, $items, true),
                    ];
                }
            }

            if (count($returnEntityData) > 0) {
                $filterModel = new Filter();
                $filterModel->setName($this->translator->trans(id: sprintf('filter.%s.description', $name), domain: 'SpyckVisualizationBundle'));
                $filterModel->setType($filter->getType());
                $filterModel->setParameter($filter->getField());
                $filterModel->setData($returnEntityData);

                $data[] = $filterModel;
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
