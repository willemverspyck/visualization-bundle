<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

final class JsonView extends AbstractSerializerView
{
    public static function getCode(): string
    {
        return ViewInterface::JSON;
    }

    public static function getContentType(): string
    {
        return 'application/json';
    }

    public static function getExtension(): string
    {
        return ViewInterface::JSON;
    }
}
