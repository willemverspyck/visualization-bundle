<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Request;

use Spyck\VisualizationBundle\Parameter\ParameterInterface;

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

    public function getDataAsString(bool $slug = false): ?string
    {
        $data = [];

        foreach ($this->getChildren() as $child) {
            if ($child instanceof ParameterInterface) {
                $data[] = $child->getDataAsString($slug);
            }
        }

        if (count($data) > 0) {
            return implode(' - ', $data);
        }

        return null;
    }
}
