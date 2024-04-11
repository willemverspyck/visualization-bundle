<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

abstract class AbstractEntityFilter extends AbstractFilter implements EntityFilterInterface
{
    private ?array $dataAsObject = null;

    public function getDataAsObject(): ?array
    {
        return $this->dataAsObject;
    }

    public function setDataAsObject(array $dataAsObject): void
    {
        $this->dataAsObject = $dataAsObject;
    }
}
