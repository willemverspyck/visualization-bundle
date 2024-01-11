<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

final class TsvView extends CsvView
{
    public static function getContentType(): string
    {
        return 'text/tab-separated-values';
    }

    public static function getExtension(): string
    {
        return ViewInterface::TSV;
    }

    public static function getName(): string
    {
        return ViewInterface::TSV;
    }

    public static function getDescription(): string
    {
        return 'TSV';
    }

    protected function getSeparator(): string
    {
        return chr(9);
    }
}
