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
use Psr\Cache\CacheItemPoolInterface;
use Spyck\VisualizationBundle\Config\Config;
use Spyck\VisualizationBundle\Field\FieldInterface;
use Spyck\VisualizationBundle\Field\MultipleFieldInterface;
use Spyck\VisualizationBundle\Format\BarFormat;
use Spyck\VisualizationBundle\Format\ConditionFormat;
use Spyck\VisualizationBundle\Format\ScaleFormat;
use Spyck\VisualizationBundle\Model\Block;
use Spyck\VisualizationBundle\Model\Dashboard;
use Spyck\VisualizationBundle\Utility\DataUtility;
use Spyck\VisualizationBundle\Utility\WidgetUtility;
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

    protected function getValue(string $type, Config $config, array|bool|DateTimeInterface|float|int|string|null $value): bool|float|int|string|null
    {
        if (null === $value) {
            return null;
        }

        return match ($type) {
            FieldInterface::TYPE_ARRAY => implode(PHP_EOL, $value),
            FieldInterface::TYPE_DATE, FieldInterface::TYPE_DATETIME, FieldInterface::TYPE_TIME => Date::dateTimeToExcel($value),
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
            $sheet
                ->getColumnDimensionByColumn($index + 1)
                ->setAutoSize(true);

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
            FieldInterface::TYPE_CURRENCY => sprintf('[$€ ] #,##0%s', null === $config->getPrecision() || 0 === $config->getPrecision() ? '' : sprintf('.%s_-', str_repeat('0', $config->getPrecision()))),
            FieldInterface::TYPE_DATE => NumberFormat::FORMAT_DATE_DDMMYYYY,
            FieldInterface::TYPE_DATETIME => sprintf('%s hh:mm:ss', NumberFormat::FORMAT_DATE_DDMMYYYY),
            FieldInterface::TYPE_NUMBER => sprintf('#,##0%s', null === $config->getPrecision() || 0 === $config->getPrecision() ? '' : sprintf('.%s_-', str_repeat('0', $config->getPrecision()))),
            FieldInterface::TYPE_PERCENTAGE => sprintf('0%s%%', null === $config->getPrecision() || 0 === $config->getPrecision() ? '' : sprintf('.%s', str_repeat('0', $config->getPrecision()))),
            FieldInterface::TYPE_TIME => 'hh:mm:ss',
            default => null,
        };
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
                $conditional->setConditionType(Conditional::CONDITION_CELLIS);
                $conditional->setOperatorType(match ($format->getOperator()) {
                    ConditionFormat::OPERATOR_EQUAL => Conditional::OPERATOR_EQUAL,
                    ConditionFormat::OPERATOR_GREATER_THAN => Conditional::OPERATOR_GREATERTHAN,
                    ConditionFormat::OPERATOR_GREATER_THAN_OR_EQUAL => Conditional::OPERATOR_GREATERTHANOREQUAL,
                    ConditionFormat::OPERATOR_LESS_THAN => Conditional::OPERATOR_LESSTHAN,
                    ConditionFormat::OPERATOR_LESS_THAN_OR_EQUAL => Conditional::OPERATOR_LESSTHANOREQUAL,
                    default => throw new Exception(sprintf('Operator "%s" not found', $format->getOperator())),
                });
                $conditional->addCondition($format->getValue() instanceof DateTimeInterface ? Date::dateTimeToExcel($format->getValue()) : $format->getValue());

                $color = $this->getColor($format->getColor()->getHex());

                if ($format->isBackground()) {
                    $conditional
                        ->getStyle()
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->setStartColor($color);
                } else {
                    $conditional
                        ->getStyle()
                        ->getFont()
                        ->setColor($color);
                }

                $content[] = $conditional;
            }

            if ($format instanceof BarFormat) {
                $color = substr($format->getColor(), 2, 6);

                $conditional = new Conditional();
                $conditional->setConditionType(Conditional::CONDITION_DATABAR);
                $conditional->setDataBar(new ConditionalDataBar());
                $conditional->getDataBar()
                    ->setMinimumConditionalFormatValueObject($this->getConditionalFormatValueObject($format->getValueMin(), 'min'))
                    ->setMaximumConditionalFormatValueObject($this->getConditionalFormatValueObject($format->getValueMax(), 'max'))
                    ->setColor($color);

                $content[] = $conditional;
            }

            if ($format instanceof ScaleFormat) {
                $conditional = new Conditional();
                $conditional->setConditionType(Conditional::CONDITION_COLORSCALE);
                $conditional->setColorScale(new ConditionalColorScale());
                $conditional->getColorScale()
                    ->setMinimumConditionalFormatValueObject($this->getConditionalFormatValueObject($format->getValueMin(), 'min'))
                    ->setMaximumConditionalFormatValueObject($this->getConditionalFormatValueObject($format->getValueMax(), 'max'))
                    ->setMinimumColor($this->getColor($format->getColorMin()->getHex()))
                    ->setMaximumColor($this->getColor($format->getColorMax()->getHex()));

                if (null !== $format->getColor()) {
                    if (null === $format->getValue()) {
                        $midpointConditionalFormatValueObject = new ConditionalFormatValueObject(type: 'percent', value: 50);
                    } else {
                        $midpointConditionalFormatValueObject = $this->getConditionalFormatValueObject($format->getValue());
                    }

                    $conditional->getColorScale()
                        ->setMidpointColor($this->getColor($format->getColor()->getHex()))
                        ->setMidpointConditionalFormatValueObject($midpointConditionalFormatValueObject);
                }


                $content[] = $conditional;
            }
        }

        return $content;
    }

    private function getColor(string $color): Color
    {
        return new Color($color);
    }

    private function getConditionalFormatValueObject(float|int|null $value, string $type = null): ConditionalFormatValueObject
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
