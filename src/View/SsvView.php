<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use DateTimeInterface;
use Spyck\VisualizationBundle\Config\Config;
use Spyck\VisualizationBundle\Field\FieldInterface;

final class SsvView extends CsvView
{
    public static function getName(): string
    {
        return ViewInterface::SSV;
    }

    protected function getSeparator(): string
    {
        return ';';
    }

    protected function getValue(string $type, Config $config, array|bool|DateTimeInterface|float|int|string|null $value): array|bool|float|int|string|null
    {
        if (null === $value) {
            return null;
        }

        return match ($type) {
            FieldInterface::TYPE_CURRENCY, FieldInterface::TYPE_NUMBER, FieldInterface::TYPE_PERCENTAGE => str_replace('.', ',', (string) $value),
            default => parent::getValue($type, $config, $value),
        };
    }
}
