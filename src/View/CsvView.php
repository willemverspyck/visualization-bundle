<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use DateTimeInterface;
use Exception;
use SplFileObject;
use Spyck\VisualizationBundle\Config\Config;
use Spyck\VisualizationBundle\Field\FieldInterface;
use Spyck\VisualizationBundle\Model\Block;
use Spyck\VisualizationBundle\Model\Dashboard;
use Spyck\VisualizationBundle\Utility\WidgetUtility;

class CsvView extends AbstractView
{
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

        ob_start();

        $splFileObject = new SplFileObject('php://output', 'w');

        $data = WidgetUtility::mapFields($widget->getFields(), function (FieldInterface $field): string {
            return $field->getName();
        });

        $splFileObject->fputcsv(fields: $data, separator: $this->getSeparator(), escape: '');

        foreach ($widget->getData() as $row) {
            $data = WidgetUtility::mapFields($widget->getFields(), function (FieldInterface $field, int $index) use ($row): bool|float|int|string|null {
                return $this->getValue($field->getType(), $field->getConfig(), $row[$index]['value']);
            });

            $splFileObject->fputcsv(fields: $data, separator: $this->getSeparator(), escape: '');
        }

        return ob_get_clean();
    }

    public static function getContentType(): string
    {
        return 'text/csv';
    }

    public static function getExtension(): string
    {
        return ViewInterface::CSV;
    }

    public static function getName(): string
    {
        return ViewInterface::CSV;
    }

    public static function isMerge(): ?bool
    {
        return false;
    }

    protected function getValue(string $type, Config $config, array|bool|DateTimeInterface|float|int|string|null $value): array|bool|float|int|string|null
    {
        if (null === $value) {
            return null;
        }

        return match ($type) {
            FieldInterface::TYPE_ARRAY => implode(', ', $value),
            FieldInterface::TYPE_BOOLEAN => $value ? 'TRUE' : 'FALSE',
            default => parent::getValue($type, $config, $value),
        };
    }

    protected function getSeparator(): string
    {
        return ',';
    }
}
