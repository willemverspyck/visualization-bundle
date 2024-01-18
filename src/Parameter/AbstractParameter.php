<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use Spyck\VisualizationBundle\Request\MultipleRequestInterface;

abstract class AbstractParameter
{
    private ?MultipleRequestInterface $parent = null;

    public function getParent(): ?MultipleRequestInterface
    {
        return $this->parent;
    }

    public function setParent(?MultipleRequestInterface $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
