<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Request;

abstract class AbstractMultipleRequest implements MultipleRequestInterface
{
    private array $children = [];

    public function addChild(RequestInterface $child): static
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}
