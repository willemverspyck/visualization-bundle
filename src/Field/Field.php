<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Field;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Spyck\ApiExtension\Model\Response;
use Spyck\VisualizationBundle\Callback\Callback;
use Spyck\VisualizationBundle\Config\Config;
use Spyck\VisualizationBundle\Format\FormatInterface;
use Spyck\VisualizationBundle\Route\RouteInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Field extends AbstractField implements FieldInterface
{
    private ?MultipleFieldInterface $parent = null;

    private Callback|string $source;

    #[Serializer\Groups(groups: Response::GROUP)]
    private string $type;

    private Config $config;

    private ?Callback $filter = null;

    /**
     * @var Collection<int, RouteInterface>
     */
    #[Serializer\Groups(groups: Response::GROUP)]
    private Collection $routes;

    public function __construct(string $name, Callback|string $source, string $type, Config $config = new Config(), ?Callback $filter = null)
    {
        parent::__construct();

        $this->routes = new ArrayCollection();

        $this->setName($name);
        $this->setSource($source);
        $this->setType($type);
        $this->setConfig($config);
        $this->setFilter($filter);
        $this->setActive(true);
    }

    public function getParent(): ?MultipleFieldInterface
    {
        return $this->parent;
    }

    public function setParent(?MultipleFieldInterface $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getSource(): Callback|string
    {
        return $this->source;
    }

    public function setSource(Callback|string $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function setConfig(Config $config): static
    {
        $config->setField($this);

        $this->config = $config;

        return $this;
    }

    public function getFilter(): ?Callback
    {
        return $this->filter;
    }

    public function setFilter(?Callback $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    public function addRoute(RouteInterface $route): static
    {
        $this->routes->add($route);

        return $this;
    }

    /**
     * @return Collection<int, RouteInterface>
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    #[Serializer\Groups(groups: Response::GROUP)]
    #[Serializer\SerializedName('config')]
    public function getConfigClass(): array
    {
        return $this->getConfig()->toArray();
    }
}
