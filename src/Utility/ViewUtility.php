<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Utility;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use Exception;
use Spyck\VisualizationBundle\Config\Config;
use Spyck\VisualizationBundle\Field\AbstractFieldInterface;
use Spyck\VisualizationBundle\Field\FieldInterface;
use Spyck\VisualizationBundle\Format\Color;
use Spyck\VisualizationBundle\Format\ConditionFormat;
use Spyck\VisualizationBundle\Format\FormatInterface;
use Spyck\VisualizationBundle\Format\ScaleFormat;
use Spyck\VisualizationBundle\Model\Aggregate;

final class ViewUtility
{
    private const string STYLE_COLOR = 'color';
    private const string STYLE_COLOR_BACKGROUND = 'background-color';
    private const string STYLE_BOLD = 'font-weight';

    public static function getNumber(Config $config, float|int $value): string
    {
        $precision = null === $config->getPrecision() ? 0 : $config->getPrecision();

        if ($config->hasAbbreviation()) {
            return NumberUtility::getAbbreviation($value, $precision);
        }

        $number = round($value, $precision);

        return number_format($number, $precision, ',', '.');
    }

    public static function getClasses(AbstractFieldInterface $field, bool $group): array
    {
        $formats = self::getFormats($field, $group);

        if ($formats->isEmpty()) {
            return [];
        }

        $data = [];

        foreach ($formats as $format) {
            $name = sprintf('format%s', ucfirst($format->getName()));

            if (false === in_array($name, $data, true)) {
                $data[] = $name;
            }
        }

        return $data;
    }

    public static function getStyles(AbstractFieldInterface $field, DateTimeInterface|float|int|string|null $value, bool $group): array
    {
        if (null === $value) {
            return [];
        }

        $formats = self::getFormats($field, $group);

        if ($formats->isEmpty()) {
            return [];
        }

        $styles = [];

        foreach ($formats as $format) {
            if ($format instanceof ConditionFormat) {
                $condition = match($format->getOperator()) {
                    ConditionFormat::OPERATOR_EQUAL => $value === $format->getValue(),
                    ConditionFormat::OPERATOR_GREATER_THAN => $value > $format->getValue(),
                    ConditionFormat::OPERATOR_GREATER_THAN_OR_EQUAL => $value >= $format->getValue(),
                    ConditionFormat::OPERATOR_LESS_THAN => $value < $format->getValue(),
                    ConditionFormat::OPERATOR_LESS_THAN_OR_EQUAL => $value <= $format->getValue(),
                    default => throw new Exception(sprintf('Operator "%s" not found', $format->getOperator())),
                };

                if ($condition) {
                    if (null !== $format->getColor() && false === array_key_exists(self::STYLE_COLOR, $styles)) {
                        $styles[self::STYLE_COLOR] = $format->getColor()->getCodeAsRgb();
                    }

                    if (null !== $format->getColorBackground() && false === array_key_exists(self::STYLE_COLOR_BACKGROUND, $styles)) {
                        $styles[self::STYLE_COLOR_BACKGROUND] = $format->getColorBackground()->getCodeAsRgb();
                    }

                    if ($format->isBold() && false === array_key_exists(self::STYLE_BOLD, $styles)) {
                        $styles[self::STYLE_BOLD] = 'bold';
                    }
                }
            }

            if ($format instanceof ScaleFormat) {
                if (false === array_key_exists(self::STYLE_COLOR_BACKGROUND, $styles)) {
                    $color = self::getColorPercentage($field, $group, $format, $value);

                    if (null !== $color) {
                        $styles[self::STYLE_COLOR_BACKGROUND] = $color->getCodeAsRgb();
                    }
                }
            }
        }

        return $styles;
    }

    private static function getAggregate(AbstractFieldInterface $field, bool $group): ?Aggregate
    {
        if (false === $group) {
            return $field->getAggregate();
        }

        if (false === $field instanceof FieldInterface || null === $field->getParent()) {
            return null;
        }

        return $field->getParent()->getAggregate();
    }

    /**
     * @return Collection<int, FormatInterface>
     */
    private static function getFormats(AbstractFieldInterface $field, bool $group): Collection
    {
        if (false === $group) {
            return $field->getFormats();
        }

        if (false === $field instanceof FieldInterface || null === $field->getParent()) {
            return new ArrayCollection();
        }

        return $field->getParent()->getFormats();
    }

    private static function getColorInterpolate(int $start, int $end, float $steps, float $count): int
    {
        $final = $start + (($end - $start) / $steps) * $count;

        return (int) floor($final);
    }

    private static function getPercentage(?Aggregate $aggregate, ScaleFormat $format, float $value): ?float
    {
        if (null === $aggregate || null === $aggregate->getMin() || null === $aggregate->getMax()) {
            return null;
        }

        $valueMin = null === $format->getValueMin() ? $aggregate->getMin() : $format->getValueMin();
        $valueMax = null === $format->getValueMax() ? $aggregate->getMax() : $format->getValueMax();

        $percentage = ($value - $valueMin) / ($valueMax - $valueMin);

        return max(min($percentage, 1), 0);
    }

    private static function getPercentageOfMidpoint(?Aggregate $aggregate, ScaleFormat $format): float
    {
        if (null === $format->getValue()) {
            if (ScaleFormat::TYPE_MEDIAN === $format->getType()) {
                return self::getPercentage($aggregate, $format, $aggregate->getMedian());
            }

            return 0.5;
        }

        return self::getPercentage($aggregate, $format, $format->getValue());
    }

    private static function getColorPercentage(AbstractFieldInterface $field, bool $group, ScaleFormat $format, float $value): ?Color
    {
        $aggregate = self::getAggregate($field, $group);

        $percentage = self::getPercentage($aggregate, $format, $value);

        if (null === $percentage) {
            return null;
        }

        $color = $format->getColor();
        $colorMin = $format->getColorMin();
        $colorMax = $format->getColorMax();

        if (null === $colorMin || null === $colorMax) {
            return null;
        }

        $colorStart = $colorMin;
        $colorEnd = $colorMax;
        $step = 1;

        if (null !== $color) {
            $percentageOfCenter = self::getPercentageOfMidpoint($aggregate, $format);

            if ($percentage < $percentageOfCenter) {
                $colorStart = $colorMin;
                $colorEnd = $color;
                $step = $percentageOfCenter;
            } else {
                $colorStart = $color;
                $colorEnd = $colorMax;
                $step = 1 - $percentageOfCenter;
                $percentage = $percentage - $percentageOfCenter;
            }
        }

        $red = self::getColorInterpolate($colorStart->getRed(), $colorEnd->getRed(), $step, $percentage);
        $green = self::getColorInterpolate($colorStart->getGreen(), $colorEnd->getGreen(), $step, $percentage);
        $blue = self::getColorInterpolate($colorStart->getBlue(), $colorEnd->getBlue(), $step, $percentage);

        return new Color($red, $green, $blue);
    }
}
