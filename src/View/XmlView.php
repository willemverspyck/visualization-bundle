<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

final class XmlView extends AbstractSerializerView
{
    public static function getContentType(): string
    {
        return 'application/xml';
    }

    public static function getExtension(): string
    {
        return ViewInterface::XML;
    }

    public static function getName(): string
    {
        return ViewInterface::XML;
    }

    public static function getDescription(): string
    {
        return 'XML';
    }
}
