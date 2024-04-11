<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\MultipleRequestInterface;

abstract class AbstractFilter implements FilterInterface
{
    private ?MultipleRequestInterface $parent = null;
    private ?array $config = null;
    private ?array $data = null;

    public function getParent(): ?MultipleRequestInterface
    {
        return $this->parent;
    }

    public function setParent(?MultipleRequestInterface $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getConfig(): ?array
    {
        return $this->config;
    }

    public function setConfig(?array $config): void
    {
        $this->config = $config;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getType(): string
    {
        return FilterInterface::TYPE_CHECKBOX;
    }

    public function preload(): bool
    {
        return true;
    }
}
