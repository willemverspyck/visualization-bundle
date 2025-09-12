<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalColorScale;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalDataBar;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormatValueObject;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Style;
use Spyck\VisualizationBundle\Config\Config;
use Spyck\VisualizationBundle\Context\ExcelContext;
use Spyck\VisualizationBundle\Field\FieldInterface;
use Spyck\VisualizationBundle\Field\MultipleFieldInterface;
use Spyck\VisualizationBundle\Format\BarFormat;
use Spyck\VisualizationBundle\Format\ConditionFormat;
use Spyck\VisualizationBundle\Format\ScaleFormat;
use Spyck\VisualizationBundle\Model\Block;
use Spyck\VisualizationBundle\Model\Dashboard;
use Spyck\VisualizationBundle\Utility\DataUtility;
use Spyck\VisualizationBundle\Utility\WidgetUtility;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class ExcelView extends AbstractView
{
    private Spreadsheet $spreadsheet;

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

    protected function getValue(string $type, Config $config, array|bool|DateTimeInterface|float|int|string|null $value): array|bool|float|int|string|null
    {
        if (null === $value) {
            return null;
        }

        return match ($type) {
            FieldInterface::TYPE_ARRAY => implode(PHP_EOL, $value),
            FieldInterface::TYPE_DATE, FieldInterface::TYPE_DATETIME, FieldInterface::TYPE_TIME => Date::dateTimeToExcel($value),
            FieldInterface::TYPE_POSITION => 0 === $value ? null : $value,
            default => $value,
        };
    }

    private function setSpreadsheet(Dashboard $dashboard): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());

        Settings::setCache($cache);

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

        $sheet->setTitle(substr($block->getName(), 0, 31));

        $widget = $block->getWidget();

        WidgetUtility::walkFields($widget->getFields(), function (FieldInterface $field, int $index) use ($sheet): void {
            $sheet->setCellValueExplicit([$index + 1, 1], $field->getName(), DataType::TYPE_STRING);
        });

        $count = 0;

        foreach ($widget->getData() as $rowIndex => $row) {
            WidgetUtility::walkFields($widget->getFields(), function (FieldInterface $field, int $index) use ($sheet, $row, $rowIndex): void {
                $value = $this->getValue($field->getType(), $field->getConfig(), $row[$index]['value']);

                $columnType = $this->getColumnType($field->getType(), $value);

                $sheet->setCellValueExplicit([$index + 1, $rowIndex + 2], $value, $columnType);
            });

            ++$count;
        }

        WidgetUtility::walkMultipleFields($widget->getFields(), function (MultipleFieldInterface $field, int $index) use ($sheet, $count): void {
            $style = $sheet->getStyle([$index + 1, 2, $index + $field->getChildren()->count(), $count + 1]);

            $this->setFieldStyle($style, $field);
        });

        WidgetUtility::walkFields($widget->getFields(), function (FieldInterface $field, int $index) use ($sheet, $count): void {
            $context = $field->getConfig()->getContext(ViewInterface::XLSX);

            $columnDimension = $sheet->getColumnDimensionByColumn($index + 1);

            if ($context instanceof ExcelContext && null !== $context->getWidth()) {
                $columnDimension->setWidth($context->getWidth());
            } else {
                $columnDimension->setAutoSize(true);
            }

            if ($context instanceof ExcelContext && false === $context->isVisible()) {
                $columnDimension->setVisible(false);
            }

            $style = $sheet->getStyle([$index + 1, 2, $index + 1, $count + 1]);

            $this->setFieldStyle($style, $field);
        });

        $sheet
            ->freezePane('A2')
            ->setAutoFilter($sheet->calculateWorksheetDimension())
            ->setSelectedCells([1, 1, 1, 1]);
    }

    private function setFieldStyle(Style $style, FieldInterface|MultipleFieldInterface $field): void
    {
        if ($field instanceof FieldInterface) {
            $columnFormat = $this->getColumnFormat($field->getType(), $field->getConfig());

            if (null !== $columnFormat) {
                $style
                    ->getNumberFormat()
                    ->setFormatCode($columnFormat);
            }
        }

        $columnConditional = $this->getColumnFormats($field->getFormats());

        if (null !== $columnConditional) {
            $style->setConditionalStyles($columnConditional);
        }
    }

    /**
     * Get the column format.
     */
    private function getColumnFormat(string $type, Config $config): ?string
    {
        return match ($type) {
            FieldInterface::TYPE_CURRENCY => sprintf('€ %s%s', $this->getNumber($config), $this->getPrecision($config)),
            FieldInterface::TYPE_DATE => NumberFormat::FORMAT_DATE_DDMMYYYY,
            FieldInterface::TYPE_DATETIME => sprintf('%s hh:mm:ss', NumberFormat::FORMAT_DATE_DDMMYYYY),
            FieldInterface::TYPE_NUMBER => sprintf('%s%s', $this->getNumber($config), $this->getPrecision($config)),
            FieldInterface::TYPE_PERCENTAGE => sprintf('0%s%%', $this->getPrecision($config)),
            FieldInterface::TYPE_POSITION => sprintf('[Color 10]▲ %s;[Color 3]▼ %s', $this->getNumber($config), $this->getNumber($config)),
            FieldInterface::TYPE_TIME => 'hh:mm:ss',
            default => null,
        };
    }

    private function getNumber(Config $config): string
    {
        return $config->hasSeparator() ? '#,##0' : '0';
    }

    private function getPrecision(Config $config): string
    {
        if (null === $config->getPrecision() || 0 === $config->getPrecision()) {
            return '';
        }

        return sprintf('.%s', str_repeat('0', $config->getPrecision()));
    }

    /**
     * Get the column conditional styles.
     */
    private function getColumnFormats(Collection $formats): ?array
    {
        if ($formats->isEmpty()) {
            return null;
        }

        $content = [];

        foreach ($formats as $format) {
            if ($format instanceof ConditionFormat) {
                $conditional = new Conditional();

                $conditional
                    ->setConditionType(Conditional::CONDITION_CELLIS)
                    ->setOperatorType(match ($format->getOperator()) {
                        ConditionFormat::OPERATOR_EQUAL => Conditional::OPERATOR_EQUAL,
                        ConditionFormat::OPERATOR_GREATER_THAN => Conditional::OPERATOR_GREATERTHAN,
                        ConditionFormat::OPERATOR_GREATER_THAN_OR_EQUAL => Conditional::OPERATOR_GREATERTHANOREQUAL,
                        ConditionFormat::OPERATOR_LESS_THAN => Conditional::OPERATOR_LESSTHAN,
                        ConditionFormat::OPERATOR_LESS_THAN_OR_EQUAL => Conditional::OPERATOR_LESSTHANOREQUAL,
                        default => throw new Exception(sprintf('Operator "%s" not found', $format->getOperator())),
                    })
                    ->addCondition($this->getConditionFormatValue($format));

                $style = $conditional->getStyle();

                if (null !== $format->getColor()) {
                    $style
                        ->getFont()
                        ->setColor(new Color($format->getColor()->getCodeAsHex()));
                }

                if (null !== $format->getColorBackground()) {
                    $style
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->setStartColor(new Color($format->getColorBackground()->getCodeAsHex()));
                }

                if ($format->isBold()) {
                    $style
                        ->getFont()
                        ->setBold(true);
                }

                $content[] = $conditional;
            }

            if ($format instanceof BarFormat) {
                $conditional = new Conditional();

                $conditional
                    ->setConditionType(Conditional::CONDITION_DATABAR)
                    ->setDataBar(new ConditionalDataBar())
                    ->getDataBar()
                        ->setMinimumConditionalFormatValueObject($this->getConditionalFormatValueObject($format->getValueMin(), 'min'))
                        ->setMaximumConditionalFormatValueObject($this->getConditionalFormatValueObject($format->getValueMax(), 'max'))
                        ->setColor($format->getColor()->getCodeAsHex());

                $content[] = $conditional;
            }

            if ($format instanceof ScaleFormat) {
                $conditional = new Conditional();

                $conditional
                    ->setConditionType(Conditional::CONDITION_COLORSCALE)
                    ->setColorScale(new ConditionalColorScale())
                    ->getColorScale()
                        ->setMinimumConditionalFormatValueObject($this->getConditionalFormatValueObject($format->getValueMin(), 'min'))
                        ->setMaximumConditionalFormatValueObject($this->getConditionalFormatValueObject($format->getValueMax(), 'max'))
                        ->setMinimumColor(new Color($format->getColorMin()->getCodeAsHex()))
                        ->setMaximumColor(new Color($format->getColorMax()->getCodeAsHex()));

                if (null !== $format->getColor()) {
                    if (null === $format->getValue()) {
                        $type = match ($format->getType()) {
                            ScaleFormat::TYPE_MEAN => 'percent',
                            ScaleFormat::TYPE_MEDIAN => 'percentile',
                            default => throw new Exception(sprintf('Type "%s" not found', $format->getType())),
                        };

                        $midpointConditionalFormatValueObject = new ConditionalFormatValueObject(type: $type, value: 50);
                    } else {
                        $midpointConditionalFormatValueObject = $this->getConditionalFormatValueObject($format->getValue());
                    }

                    $conditional->getColorScale()
                        ->setMidpointColor(new Color($format->getColor()->getCodeAsHex()))
                        ->setMidpointConditionalFormatValueObject($midpointConditionalFormatValueObject);
                }

                $content[] = $conditional;
            }
        }

        return $content;
    }

    private function getConditionFormatValue(ConditionFormat $format): array|bool|float|int|string|null
    {
        $value = $format->getValue();

        if ($value instanceof DateTimeInterface) {
            return Date::dateTimeToExcel($value);
        }

        return $value;
    }

    private function getConditionalFormatValueObject(float|int|null $value, ?string $type = null): ConditionalFormatValueObject
    {
        if (null === $value) {
            DataUtility::assert(null !== $type, new Exception('Type not found'));

            return new ConditionalFormatValueObject(type: $type);
        }

        return new ConditionalFormatValueObject(type: 'num', value: $value);
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
            FieldInterface::TYPE_ARRAY, FieldInterface::TYPE_IMAGE, FieldInterface::TYPE_TEXT => DataType::TYPE_STRING,
            FieldInterface::TYPE_BOOLEAN => DataType::TYPE_BOOL,
            default => DataType::TYPE_NUMERIC,
        };
    }
}
