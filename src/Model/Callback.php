<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

final class Callback
{
    private $name;
    private array $parameters;

    public function __construct(callable $name, array $parameters = [])
    {
        $this->setName($name);
        $this->setParameters($parameters);
    }

    public function getName(): callable
    {
        return $this->name;
    }

    public function setName(callable $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }
}
