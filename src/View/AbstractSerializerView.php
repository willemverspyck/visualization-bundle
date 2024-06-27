<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use DateTimeInterface;
use Exception;
use Spyck\ApiExtension\Model\Response;
use Spyck\VisualizationBundle\Config\Config;
use Spyck\VisualizationBundle\Field\FieldInterface;
use Spyck\VisualizationBundle\Model\Block;
use Spyck\VisualizationBundle\Model\Dashboard;
use Spyck\VisualizationBundle\Utility\WidgetUtility;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
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

        $fields = WidgetUtility::mapFields($widget->getFields(), function (FieldInterface $field): FieldInterface {
            return $field;
        });

        $pagination = $widget->getPagination();

        $content = [
            'data' => [],
            'total' => $widget->getTotal(),
            'fields' => $fields,
            'properties' => $widget->getProperties(),
            'events' => $widget->getEvents(),
            'parameters' => $widget->getParameters(),
            'filters' => $widget->getFilters(),
            'pagination' => null === $pagination ? null : [
                'previous' => $pagination->getPrevious(),
                'next' => $pagination->getNext(),
            ],
        ];

        foreach ($widget->getData() as $row) {
            $content['data'][] = WidgetUtility::mapFields($widget->getFields(), function (FieldInterface $field, int $index) use ($row): array {
                return [
                    'value' => $this->getValue($field->getType(), $field->getConfig(), $row[$index]['value']),
                    'valueFormat' => $this->getValueFormat($field->getType(), $field->getConfig(), $row[$index]['value']),
                    'routes' => $row[$index]['routes'],
                ];
            });
        }

        return $this->serializer->serialize($content, $this->getExtension(), [
            AbstractObjectNormalizer::GROUPS => [
                Response::GROUP,
            ],
        ]);
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
            FieldInterface::TYPE_BOOLEAN => $value ? '✓' : '✕',
            FieldInterface::TYPE_CURRENCY => sprintf('€ %s', $this->getValueOfNumber($config, $value)),
            FieldInterface::TYPE_NUMBER => $this->getValueOfNumber($config, $value),
            FieldInterface::TYPE_PERCENTAGE => sprintf('%s%%', $this->getValueOfNumber($config, $value * 100)),
            default => parent::getValue($type, $config, $value),
        };
    }
}
