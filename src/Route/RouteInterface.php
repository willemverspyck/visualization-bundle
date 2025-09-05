<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Route;

interface RouteInterface
{
    public function getName(): ?string;

    public function getUrl(): ?string;

    public function getVariables(): array;
}
