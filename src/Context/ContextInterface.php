<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Context;

interface ContextInterface
{
    public function getView(): string;

    public function setView(string $view): static;
}
