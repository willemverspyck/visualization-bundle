<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

final class Route implements RouteInterface
{
    private ?string $name = null;
    private ?string $url = null;
    private array $parameters = [];

    public function __construct(string $name, string $url, array $parameters = [])
    {
        $this->setName($name);
        $this->setUrl($url);
        $this->setParameters($parameters);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

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
