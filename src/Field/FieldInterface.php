<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Field;

use Doctrine\Common\Collections\Collection;
use Spyck\VisualizationBundle\Callback\Callback;
use Spyck\VisualizationBundle\Config\Config;
use Spyck\VisualizationBundle\Route\RouteInterface;

interface FieldInterface extends AbstractFieldInterface
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

    public function getParent(): ?MultipleFieldInterface;

    public function setParent(?MultipleFieldInterface $parent): static;

    public function getSource(): Callback|string;

    public function setSource(Callback|string $source): static;

    public function getType(): string;

    public function setType(string $type): static;

    public function getConfig(): Config;

    public function setConfig(Config $config): static;

    public function getFilter(): ?Callback;

    public function setFilter(Callback $callback): static;

    public function addRoute(RouteInterface $route): static;

    public function getRoutes(): Collection;
}
