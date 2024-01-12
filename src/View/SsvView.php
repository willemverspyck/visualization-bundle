<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

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
}
