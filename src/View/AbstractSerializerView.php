<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use Spyck\ApiExtension\Model\Response;
use Spyck\VisualizationBundle\Model\Block;
use Spyck\VisualizationBundle\Model\Config;
use Spyck\VisualizationBundle\Model\Dashboard;
use DateTimeInterface;
use Exception;
use Spyck\VisualizationBundle\Model\Field;
use Spyck\VisualizationBundle\Model\Widget;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractSerializerView extends AbstractView
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    /**
     * @throws Exception
     */
    public function getContent(Dashboard $dashboard): string
    {
        $block = $dashboard->getBlocks()->first();

        if (false === $block instanceof Block) {
            throw new Exception('No "Block" model');
        }

        $widget = $block->getWidget();

        $pagination = $widget->getPagination();

        $content = [
            'data' => [],
            'total' => $widget->getTotal(),
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

            foreach ($fields as $fieldIndex => $field) {
                $cell = $row['fields'][$fieldIndex];

                $data[] = [
                    'value' => $this->getValue($field['type'], $field['config'], $cell['value']),
                    'valueFormat' => $this->getValueFormat($field['type'], $field['config'], $cell['value']),
                    'routes' => $cell['routes'],
                    'overlays' => $cell['children'],
                ];
            }

            $content['data'][] = $data;
        }

        return $this->serializer->serialize($content, $this->getExtension());
    }

    public static function isMerge(): ?bool
    {
        return false;
    }

    /**
     * @throws Exception
     */
    private function getValueFormat(string $type, Config $config, array|bool|DateTimeInterface|float|int|string|null $value): bool|float|int|string|null
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
