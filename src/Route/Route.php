<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Route;

final class Route implements RouteInterface
{
    private ?string $name = null;
    private ?string $url = null;
    private array $variables = [];

    public function __construct(string $name, string $url, array $variables = [])
    {
        $this->setName($name);
        $this->setUrl($url);
        $this->setVariables($variables);
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

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): static
    {
        $this->variables = $variables;

        return $this;
    }
}
