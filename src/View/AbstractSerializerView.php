<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use DateTimeInterface;
use Exception;
use Spyck\ApiExtension\Model\Response;
use Spyck\VisualizationBundle\Config\Config;
use Spyck\VisualizationBundle\Controller\WidgetController;
use Spyck\VisualizationBundle\Field\FieldInterface;
use Spyck\VisualizationBundle\Field\MultipleFieldInterface;
use Spyck\VisualizationBundle\Model\Block;
use Spyck\VisualizationBundle\Model\Dashboard;
use Spyck\VisualizationBundle\Utility\WidgetUtility;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
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

        return $this->serializer->serialize($widget, $this->getExtension(), [
            AbstractObjectNormalizer::GROUPS => [
                WidgetController::GROUP_ITEM,
            ],
            AbstractNormalizer::CALLBACKS => [
                'data' => function (array $data) use ($widget): iterable {
                    foreach ($data as $row) {
                        yield WidgetUtility::mapFields($widget->getFields(), function (FieldInterface $field, int $index) use ($row): array {
                            return [
                                'value' => $this->getValue($field->getType(), $field->getConfig(), $row[$index]['value']),
                                'valueFormat' => $this->getValueFormat($field->getType(), $field->getConfig(), $row[$index]['value']),
                                'routes' => $row[$index]['routes'],
                            ];
                        });
                    }
                },
                'fields' => function (array $fields): iterable {
                    foreach ($fields as $field) {
                        if ($field->isActive()) {
                            yield $field;
                        }
                    }
                },
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
