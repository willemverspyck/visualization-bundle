<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\RequestInterface;

interface FilterInterface extends RequestInterface
{
    public const string TYPE_CHECKBOX = 'checkbox';
    public const string TYPE_NUMBER = 'number';
    public const string TYPE_PERCENTAGE = 'percentage';
    public const string TYPE_SELECT = 'select';
    public const string TYPE_TEXT = 'text';

    public function getConfig(): ?array;

    public function setConfig(?array $config): void;

    public function getData(): ?array;

    public function setData(array $data): void;

    public function getType(): string;

    public function preload(): bool;
}
