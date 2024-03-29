<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\RequestInterface;

interface FilterInterface extends RequestInterface
{
    public const TYPE_CHECKBOX = 'checkbox';
    public const TYPE_INPUT = 'input';
    public const TYPE_SELECT = 'select';

    public function getConfig(): ?array;

    public function setConfig(?array $config): void;

    public function getData(): ?array;

    public function setData(array $data): void;

    public function preload(): bool;
}
