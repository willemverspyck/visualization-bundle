<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Request;

interface MultipleRequestInterface
{
    public function addChild(RequestInterface $child): static;

    /**
     * @return array<int, RequestInterface>
     */
    public function getChildren(): array;

    public function getDataAsString(bool $slug = false): ?string;
}
