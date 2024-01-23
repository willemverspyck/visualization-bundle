<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

abstract class AbstractEntityFilter extends AbstractFilter implements EntityFilterInterface
{
    public function __construct()
    {
        $this->setConfig([
            'type' => FilterInterface::TYPE_CHECKBOX,
        ]);
    }

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
