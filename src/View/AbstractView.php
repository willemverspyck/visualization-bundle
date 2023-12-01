<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use DateTimeInterface;
use Exception;
use Spyck\VisualizationBundle\Model\Config;
use Spyck\VisualizationBundle\Model\Field;
use Spyck\VisualizationBundle\Utility\NumberUtility;
use Symfony\Component\String\Slugger\AsciiSlugger;

abstract class AbstractView implements ViewInterface
{
    public function getFile(string $name, array $parameters): string
    {
        $slugger = new AsciiSlugger();

        $filename = [
            $name,
        ];

        foreach ($parameters as $parameter) {
            $filename[] = sprintf('%s', $parameter);
        }

        return $slugger->slug(implode('-', $filename))->lower()->toString();
    }

    /**
     * {@inheritDoc}
     */
    public static function isMerge(): ?bool
    {
        return null;
    }

    /**
     * @throws Exception
     */
    protected function getValue(string $type, Config $config, array|bool|DateTimeInterface|float|int|string|null $value): bool|float|int|string|null
    {
        if (null === $value) {
            return null;
        }

        return match ($type) {
            Field::TYPE_ARRAY => implode(', ', $value),
            Field::TYPE_BOOLEAN => $value ? 'TRUE' : 'FALSE',
            Field::TYPE_DATE => $value->format('Y-m-d'),
            Field::TYPE_DATETIME => $value->format('Y-m-d H:i:s'),
            Field::TYPE_TIME => $value->format('H:i:s'),
            Field::TYPE_POSITION => sprintf('/images/position_%s.png', 0.0 === $value ? 'equal' : ($value > 0 ? 'greater' : 'less')),
            default => $value,
        };
    }

    /**
     * @throws Exception
     */
    protected function getValueOfNumber(Config $config, float|int $value): string
    {
        $precision = null !== $config->getPrecision() ? $config->getPrecision() : 0;

        if ($config->hasAbbreviation()) {
            return NumberUtility::getAbbreviation($value, $precision);
        }

        $number = round($value, $precision);

        return number_format($number, $precision, ',', '.');
    }
}
