<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

interface EntityFilterInterface extends FilterInterface
{
    public function getDataAsObject(): ?array;

    public function setDataAsObject(array $dataAsObject): void;
}
