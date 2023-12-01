<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

interface RouteInterface
{
    public function getName(): string;

    public function getUrl(): string;

    public function getParameters(): array;
}
