<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Utility;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Spyck\VisualizationBundle\Config\Config;
use Spyck\VisualizationBundle\Field\AbstractFieldInterface;
use Spyck\VisualizationBundle\Field\FieldInterface;
use Spyck\VisualizationBundle\Format\ConditionFormat;
use Spyck\VisualizationBundle\Format\FormatInterface;
use Spyck\VisualizationBundle\Format\ScaleFormat;
use Spyck\VisualizationBundle\Model\Aggregate;

final class ViewUtility
{
    private const string STYLE_BACKGROUND_COLOR = 'background-color';
    private const string STYLE_COLOR = 'color';

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

    public static function getStyles(AbstractFieldInterface $field, $value, bool $group): array
    {
        $formats = self::getFormats($field, $group);

        if ($formats->isEmpty()) {
            return [];
        }

        if (null === $value) {
            return [];
        }

        $styles = [];

        foreach ($formats as $format) {
            if ($format instanceof ScaleFormat) {
                if (false === array_key_exists(self::STYLE_BACKGROUND_COLOR, $styles)) {
                    $aggregate = self::getAggregate($field, $group);
                    $color = self::getColorPercentage($aggregate, $format, $value);

                    if (null !== $color) {
                        $styles[self::STYLE_BACKGROUND_COLOR] = $color;
                    }
                }
            }
        }

        foreach ($formats as $format) {
            if ($format instanceof ConditionFormat) {
                $condition = match($format->getOperator()) {
                    ConditionFormat::OPERATOR_EQUAL => $value === $format->getValue(),
                    ConditionFormat::OPERATOR_GREATER_THAN => $value > $format->getValue(),
                    ConditionFormat::OPERATOR_GREATER_THAN_OR_EQUAL => $value >= $format->getValue(),
                    ConditionFormat::OPERATOR_LESS_THAN => $value < $format->getValue(),
                    ConditionFormat::OPERATOR_LESS_THAN_OR_EQUAL => $value <= $format->getValue(),
                    default => false,
                };

                $name = $format->isBackground() ? self::STYLE_BACKGROUND_COLOR : self::STYLE_COLOR;

                if ($condition && false === array_key_exists($name, $styles)) {
                    $color = $format->getColor();

                    $styles[$name] = $color->getHex();
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
            return ScaleFormat::TYPE_MEDIAN === $format->getType() ? self::getPercentage($aggregate, $format, $aggregate->getMedian()) : 0.5;
        }

        return self::getPercentage($aggregate, $format, $format->getValue());
    }

    private static function getColorPercentage(?Aggregate $aggregate, ScaleFormat $format, float $value): ?string
    {
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

        return sprintf('#%02x%02x%02x', $red, $green, $blue);
    }
}
