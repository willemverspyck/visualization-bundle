<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Request;

interface RequestInterface
{
    public const string DATE = 'date';
    public const string DATE_START = 'dateStart';
    public const string DATE_END = 'dateEnd';
    public const string LIMIT = 'limit';
    public const string OFFSET = 'offset';
    public const string OPTION = 'option';

    public function getParent(): ?MultipleRequestInterface;

    public static function getField(): string;

    public static function getName(): string;
}
