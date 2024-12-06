<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Widget;

use Spyck\VisualizationBundle\Field\FieldInterface;
use Spyck\VisualizationBundle\Field\MultipleFieldInterface;

final class SkeletonWidget extends AbstractWidget implements WidgetInterface
{
    private array $data = [];
    private array $fields = [];

    public function getData(): iterable
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return iterable<int, FieldInterface|MultipleFieldInterface>
     */
    public function getFields(): iterable
    {
        return $this->fields;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }
}
