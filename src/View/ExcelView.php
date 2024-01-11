<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalDataBar;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormatValueObject;
use Spyck\VisualizationBundle\Model\Block;
use Spyck\VisualizationBundle\Model\ConditionFormat;
use Spyck\VisualizationBundle\Model\Config;
use Spyck\VisualizationBundle\Model\Dashboard;
use Spyck\VisualizationBundle\Model\DatabarFormat;
use Spyck\VisualizationBundle\Model\Field;
use DateTimeInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Psr16Cache;

final class ExcelView extends AbstractView
{
    private Spreadsheet $spreadsheet;

    public function __construct(private readonly CacheItemPoolInterface $cacheItemPool)
    {
    }

    /**
     * @throws Exception
     */
    public function getContent(Dashboard $dashboard): string
    {
        $this->setSpreadsheet($dashboard);

        $dashboard->getBlocks()->forAll(function (int $index, Block $block): bool {
            $this->addSheet($index, $block);

            return true;
        });

        $this->spreadsheet->setActiveSheetIndex(0);

        ob_start();

        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $writer->save('php://output');

        return ob_get_clean();
    }

    public static function getContentType(): string
    {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }

    public static function getExtension(): string
    {
        return ViewInterface::XLSX;
    }

    public static function getName(): string
    {
        return ViewInterface::XLSX;
    }

    public static function getDescription(): string
    {
        return 'Microsoft Excel';
    }

    /**
     * {@inheritDoc}
     */
    protected function getValue(string $type, Config $config, array|bool|DateTimeInterface|float|int|string|null $value): bool|float|int|string|null
    {
        if (null === $value) {
            return null;
        }

        return match ($type) {
            Field::TYPE_ARRAY => implode(PHP_EOL, $value),
            Field::TYPE_DATE, Field::TYPE_DATETIME, Field::TYPE_TIME => Date::dateTimeToExcel($value),
            default => $value,
        };
    }

    private function setSpreadsheet(Dashboard $dashboard): void
    {
        $psr16Cache = new Psr16Cache($this->cacheItemPool);

        Settings::setCache($psr16Cache);

        $this->spreadsheet = new Spreadsheet();

        $properties = $this->spreadsheet->getProperties();
        $properties->setTitle($dashboard->getName());

        if (null !== $dashboard->getDescription()) {
            $properties->setDescription($dashboard->getDescription());
        }

        $properties->setCreator($dashboard->getUser());

        $copyright = $dashboard->getCopyright();

        if (null !== $copyright) {
            $properties->setCompany($copyright);
        }
    }

    /**
     * @throws Exception
     */
    private function addSheet(int $index, Block $block): void
    {
        if ($index > 0) {
            $sheet = $this->spreadsheet->createSheet();
        } else {
            $sheet = $this->spreadsheet->getActiveSheet();
        }

        $sheet->setTitle(substr($block->getName(), 0, 24));

        $widget = $block->getWidget();

        $fields = $widget->getFields();

        foreach ($fields as $fieldIndex => $field) {
            $sheet->setCellValueExplicit([$fieldIndex + 1, 1], $field['name'], DataType::TYPE_STRING);

            $style = $sheet->getStyle([$fieldIndex + 1, 2, $fieldIndex + 1, count($widget->getData()) + 1]);

            $columnFormat = $this->getColumnFormat($field['type'], $field['config']);

            if (null !== $columnFormat) {
                $style
                    ->getNumberFormat()
                    ->setFormatCode($columnFormat);
            }

            $columnConditional = $this->getColumnFormats($field['type'], $field['config']);

            if (null !== $columnConditional) {
                $style
                    ->setConditionalStyles($columnConditional);
            }
        }

        foreach ($widget->getData() as $rowIndex => $row) {
            foreach ($row['fields'] as $fieldIndex => $field) {
                $value = $this->getValue($fields[$fieldIndex]['type'], $fields[$fieldIndex]['config'], $field['value']);

                $columnType = $this->getColumnType($fields[$fieldIndex]['type'], $value);

                $sheet->setCellValueExplicit([$fieldIndex + 1, $rowIndex + 2], $value, $columnType);
            }
        }

        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        $sheet->setSelectedCells([1, 1, 1, 1]);
    }

    /**
     * Get the column format.
     */
    private function getColumnFormat(string $type, Config $config): ?string
    {
        return match ($type) {
            Field::TYPE_CURRENCY => sprintf('[$€ ] #,##0%s', null === $config->getPrecision() ? '' : sprintf('.%s_-', str_repeat('0', $config->getPrecision()))),
            Field::TYPE_DATE => NumberFormat::FORMAT_DATE_DDMMYYYY,
            Field::TYPE_DATETIME => sprintf('%s hh:mm:ss', NumberFormat::FORMAT_DATE_DDMMYYYY),
            Field::TYPE_NUMBER => sprintf('#,##0%s', null === $config->getPrecision() ? '' : sprintf('.%s_-', str_repeat('0', $config->getPrecision()))),
            Field::TYPE_PERCENTAGE => sprintf('0%s%%', null === $config->getPrecision() ? '' : sprintf('.%s', str_repeat('0', $config->getPrecision()))),
            Field::TYPE_TIME => 'hh:mm:ss',
            default => null,
        };
    }

    /**
     * Get the column conditional styles.
     */
    private function getColumnFormats(string $type, Config $config): ?array
    {
        $content = null;

        switch ($type) {
            case Field::TYPE_CURRENCY:
            case Field::TYPE_PERCENTAGE:
            case Field::TYPE_NUMBER:
                $formats = $config->getFormats();

                if (false === $formats->isEmpty()) {
                    $content = [];

                    foreach ($formats as $format) {
                        if ($format instanceof ConditionFormat) {
                            $conditional = new Conditional();
                            $conditional->setConditionType(Conditional::CONDITION_CELLIS);

                            if (null !== $format->getStart() && null !== $format->getEnd()) {
                                $conditional->setOperatorType(Conditional::OPERATOR_BETWEEN);
                                $conditional->addCondition($format->getStart());
                                $conditional->addCondition($format->getEnd());
                            } elseif (null !== $format->getStart()) {
                                $conditional->setOperatorType(Conditional::OPERATOR_GREATERTHANOREQUAL);
                                $conditional->addCondition($format->getStart());
                            } elseif (null !== $format->getEnd()) {
                                $conditional->setOperatorType(Conditional::OPERATOR_LESSTHAN);
                                $conditional->addCondition($format->getEnd());
                            } else {
                                $conditional->setOperatorType(Conditional::OPERATOR_NONE);
                            }

                            $conditional->getStyle()->getFont()->getColor()->setRGB($format->getColor());

                            $content[] = $conditional;
                        }

                        if ($format instanceof DatabarFormat) {
                            $conditional = new Conditional();
                            $conditional->setConditionType(Conditional::CONDITION_DATABAR);
                            $conditional->setDataBar(new ConditionalDataBar());
                            $conditional->getDataBar()
                                ->setMinimumConditionalFormatValueObject(new ConditionalFormatValueObject('min'))
                                ->setMaximumConditionalFormatValueObject(new ConditionalFormatValueObject('max'))
                                ->setColor($format->getColor());

                            $content[] = $conditional;
                        }
                    }
                }

                break;
        }

        return $content;
    }

    /**
     * Get the column format.
     */
    private function getColumnType(string $type, bool|float|int|string|null $value): string
    {
        if (null === $value) {
            return DataType::TYPE_NULL;
        }

        return match ($type) {
            Field::TYPE_ARRAY, Field::TYPE_IMAGE, Field::TYPE_TEXT => DataType::TYPE_STRING,
            Field::TYPE_BOOLEAN => DataType::TYPE_BOOL,
            default => DataType::TYPE_NUMERIC,
        };
    }
}
