<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

abstract class AbstractEntityParameter extends AbstractParameter implements EntityParameterInterface
{
    private ?int $data = null;
    private ?object $dataAsObject = null;
    private bool $request = false;

    public function getData(): ?int
    {
        return $this->data;
    }

    public function getDataAsObject(): ?object
    {
        return $this->dataAsObject;
    }

    public function getGroup(): ?string
    {
        return null;
    }

    public function getRoute(): ?string
    {
        return null;
    }

    public function isRequest(): bool
    {
        return $this->request;
    }

    public function setData(string $data): void
    {
        $this->data = intval($data);
    }

    public function setDataAsObject(?object $dataAsObject): void
    {
        $this->dataAsObject = $dataAsObject;
    }

    public function setRequest(bool $request): void
    {
        $this->request = $request;
    }
}
