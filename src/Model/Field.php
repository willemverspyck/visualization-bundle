<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Spyck\ApiExtension\Model\Response;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Field
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_NUMBER = 'number';
    public const TYPE_CURRENCY = 'currency';
    public const TYPE_POSITION = 'position';
    public const TYPE_ARRAY = 'array';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_DATE = 'date';
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_TEXT = 'text';
    public const TYPE_TIME = 'time';

    private ?Field $parent = null;

    #[Serializer\Groups(groups: Response::GROUP)]
    private string $name;

    #[Serializer\Groups(groups: Response::GROUP)]
    private Callback|string $source;

    #[Serializer\Groups(groups: Response::GROUP)]
    private string $type;

    private Config $config;
    private ?Callback $filter = null;

    /**
     * @var Collection<int, Field>
     */
    private Collection $children;

    /**
     * @var Collection<int, RouteInterface>
     */
    private Collection $routes;

    public function __construct(string $name, Callback|string $source, string $type, Config $config = new Config(), ?Callback $filter = null)
    {
        $this->children = new ArrayCollection();
        $this->routes = new ArrayCollection();

        $this->setName($name);
        $this->setSource($source);
        $this->setType($type);
        $this->setConfig($config);
        $this->setFilter($filter);
    }

    public function getParent(): ?Field
    {
        return $this->parent;
    }

    public function setParent(?Field $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function addChild(Field $child): static
    {
        $this->children->add($child);

        return $this;
    }

    public function clearChildren(): void
    {
        $this->children->clear();
    }

    /**
     * @return Collection<int, Field>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function removeChild(Field $child): void
    {
        $this->children->removeElement($child);
    }

    public function addRoute(RouteInterface $route): static
    {
        $this->routes->add($route);

        return $this;
    }

    public function clearRoutes(): void
    {
        $this->routes->clear();
    }

    /**
     * @return Collection<int, RouteInterface>
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    public function removeRoute(RouteInterface $route): void
    {
        $this->routes->removeElement($route);
    }
}
