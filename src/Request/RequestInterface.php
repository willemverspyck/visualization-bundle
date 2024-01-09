<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Request;

interface RequestInterface
{
    public const DATE = 'date';
    public const DATE_START = 'dateStart';
    public const DATE_END = 'dateEnd';
    public const LIMIT = 'limit';
    public const OFFSET = 'offset';
    public const OPTION = 'option';

    public static function getField(): string;

    public static function getName(): string;
}
