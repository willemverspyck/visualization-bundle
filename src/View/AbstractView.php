<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use DateTimeInterface;
use Exception;
use Spyck\VisualizationBundle\Config\Config;
use Spyck\VisualizationBundle\Field\FieldInterface;
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
            FieldInterface::TYPE_ARRAY => implode(', ', $value),
            FieldInterface::TYPE_DATE => $value->format('Y-m-d'),
            FieldInterface::TYPE_DATETIME => $value->format('Y-m-d H:i:s'),
            FieldInterface::TYPE_TIME => $value->format('H:i:s'),
            default => $value,
        };
    }
}
