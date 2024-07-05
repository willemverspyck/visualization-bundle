<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Format;

use Spyck\VisualizationBundle\Controller\WidgetController;
use Symfony\Component\Serializer\Annotation as Serializer;

final class BarFormat implements FormatInterface
{
    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private string $color;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private float|int|null $valueMin = null;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private float|int|null $valueMax = null;

    public function __construct(string $color, float|int|null $valueMin = null, float|int|null $valueMax = null)
    {
        $this->setColor($color);
        $this->setValueMin($valueMin);
        $this->setValueMax($valueMax);
    }

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    public function getName(): string
    {
        return 'bar';
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getValueMin(): float|int|null
    {
        return $this->valueMin;
    }

    public function setValueMin(float|int|null $valueMin): static
    {
        $this->valueMin = $valueMin;

        return $this;
    }

    public function getValueMax(): float|int|null
    {
        return $this->valueMax;
    }

    public function setValueMax(float|int|null $valueMax): static
    {
        $this->valueMax = $valueMax;

        return $this;
    }
}
