<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use Spyck\VisualizationBundle\Model\Block;
use Spyck\VisualizationBundle\Model\Config;
use Spyck\VisualizationBundle\Model\Dashboard;
use DateTimeInterface;
use Exception;
use SplFileObject;
use Spyck\VisualizationBundle\Model\Field;

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

        $fields = $widget->getFields();

        $data = [];

        foreach ($fields as $field) {
            $data[] = $field['name'];
        }

        $splFileObject->fputcsv($data, $this->getSeparator());

        foreach ($widget->getData() as $row) {
            $data = [];

            foreach ($row['fields'] as $fieldIndex => $field) {
                $data[] = $this->getValue($fields[$fieldIndex]['type'], $fields[$fieldIndex]['config'], $field['value']);
            }

            $splFileObject->fputcsv($data, $this->getSeparator());
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

    public static function getDescription(): string
    {
        return 'CSV (Comma)';
    }

    /**
     * {@inheritDoc}
     */
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
            Field::TYPE_BOOLEAN => $value ? 'TRUE' : 'FALSE',
            default => parent::getValue($type, $config, $value),
        };
    }

    protected function getSeparator(): string
    {
        return ',';
    }
}
