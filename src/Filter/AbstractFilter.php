<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\MultipleRequestInterface;

abstract class AbstractFilter implements FilterInterface
{
    private ?MultipleRequestInterface $parent = null;
    private ?array $data = null;
    private string $type;

    public function getParent(): ?MultipleRequestInterface
    {
        return $this->parent;
    }

    public function setParent(?MultipleRequestInterface $parent): self
    {
        $this->parent = $parent;

        return $this;
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
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
