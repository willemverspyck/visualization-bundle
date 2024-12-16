<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Context;

abstract class AbstractContext implements ContextInterface
{
    private string $view;

    public function getView(): string
    {
        return $this->view;
    }

    public function setView(string $view): static
    {
        $this->view = $view;

        return $this;
    }
}
