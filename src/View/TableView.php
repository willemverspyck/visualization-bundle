<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use DateTimeInterface;
use Exception;
use Spyck\VisualizationBundle\Model\Config;
use Spyck\VisualizationBundle\Model\Dashboard;
use Spyck\VisualizationBundle\Model\Field;
use Spyck\VisualizationBundle\Model\Widget;

final class TableView extends AbstractView
{
    /**
     * @throws Exception
     */
    public function getContent(Dashboard $dashboard): string
    {
        $blocks = $dashboard->getBlocks();

        if ($blocks->count() > 0) {
            $block = $blocks->first();

            return $this->getWidget($block->getWidget());
        }

        throw new Exception('No "Block" model');
    }

    /**
     * @throws Exception
     */
    public function getWidget(Widget $widget): string
    {
        $pagination = $widget->getPagination();

        $content = [
            'data' => [],
            'fields' => [],
            'properties' => $widget->getProperties(),
            'events' => $widget->getEvents(),
            'parameters' => $widget->getParameters(),
            'filters' => $widget->getFilters(),
            'pagination' => null === $pagination ? null : [
                'previous' => $pagination->getPrevious(),
                'next' => $pagination->getNext(),
            ],
        ];

        $fields = $widget->getFields();

        foreach ($fields as $field) {
            $content['fields'][] = [
                'name' => $field['name'],
                'type' => $field['type'],
                'config' => $field['config']->toArray(),
                'children' => $field['children'],
            ];
        }

        foreach ($widget->getData() as $row) {
            $data = [];

            foreach ($row['fields'] as $fieldIndex => $field) {
                $data[] = [
                    'value' => $this->getValue($fields[$fieldIndex]['type'], $fields[$fieldIndex]['config'], $field['value']),
                    'valueFormat' => $this->getValueFormat($fields[$fieldIndex]['type'], $fields[$fieldIndex]['config'], $field['value']),
                    'routes' => $field['routes'],
                    'overlays' => $field['children'],
                ];
            }

            $content['data'][] = $data;
        }

        return json_encode($content);
    }

    public static function getContentType(): string
    {
        return 'application/json';
    }

    public static function getExtension(): string
    {
        return 'table';
    }

    public static function getName(): string
    {
        return 'table';
    }

    public static function isMerge(): ?bool
    {
        return false;
    }

    protected function getValue(string $type, Config $config, array|bool|DateTimeInterface|float|int|string|null $value): bool|float|int|string|null
    {
        if (null === $value) {
            return null;
        }

        return match ($type) {
            Field::TYPE_ARRAY => implode(', ', $value),
            Field::TYPE_DATE, Field::TYPE_DATETIME, Field::TYPE_TIME => $value->format('Y-m-d H:i:s'),
            default => $value,
        };
    }

    /**
     * @throws Exception
     */
    protected function getValueFormat(string $type, Config $config, array|bool|DateTimeInterface|float|int|string|null $value): bool|float|int|string|null
    {
        if (null === $value) {
            return null;
        }

        return match ($type) {
            Field::TYPE_BOOLEAN => $value ? '✓' : '✕',
            Field::TYPE_CURRENCY => sprintf('€ %s', $this->getValueOfNumber($config, $value)),
            Field::TYPE_NUMBER => $this->getValueOfNumber($config, $value),
            Field::TYPE_PERCENTAGE => sprintf('%s%%', $this->getValueOfNumber($config, $value * 100)),
            default => parent::getValue($type, $config, $value),
        };
    }
}
