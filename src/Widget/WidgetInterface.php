<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Widget;

use Spyck\VisualizationBundle\Entity\Widget;
use Spyck\VisualizationBundle\Field\FieldInterface;
use Spyck\VisualizationBundle\Field\MultipleFieldInterface;
use Spyck\VisualizationBundle\Request\MultipleRequestInterface;
use Spyck\VisualizationBundle\Request\RequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['spyck.visualization.widget'])]
interface WidgetInterface
{
    public function getCache(): ?int;

    public function getData(): iterable;

    public function getTotal(): ?int;

    public function getEvents(): iterable;

    /**
     * @return iterable<int, FieldInterface|MultipleFieldInterface>
     */
    public function getFields(): iterable;

    /**
     * @return iterable<RequestInterface|MultipleRequestInterface>
     */
    public function getFilters(): iterable;

    /**
     * @return iterable<RequestInterface|MultipleRequestInterface>
     */
    public function getParameters(): iterable;

    public function getProperties(): iterable;

    public function getType(): ?string;

    public function mapData(array $data): array;

    public function setView(?string $view): static;

    public function getView(): ?string;

    public function setWidget(Widget $widget): static;

    public function getWidget(): Widget;
}
