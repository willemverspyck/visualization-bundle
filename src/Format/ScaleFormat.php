<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Format;

use Spyck\VisualizationBundle\Controller\WidgetController;
use Symfony\Component\Serializer\Attribute as Serializer;

final class ScaleFormat implements FormatInterface
{
    public const string TYPE_MEAN = 'mean';
    public const string TYPE_MEDIAN = 'median';

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private ?Color $color = null;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private Color $colorMin;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private Color $colorMax;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private string $type = self::TYPE_MEAN;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private float|int|null $value = null;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private float|int|null $valueMin = null;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private float|int|null $valueMax = null;

    public function __construct(?Color $color, Color $colorMin, Color $colorMax, string $type = self::TYPE_MEAN, float|int|null $value = null, float|int|null $valueMin = null, float|int|null $valueMax = null)
    {
        $this->setColor($color);
        $this->setColorMin($colorMin);
        $this->setColorMax($colorMax);
        $this->setType($type);
        $this->setValue($value);
        $this->setValueMin($valueMin);
        $this->setValueMax($valueMax);
    }

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    public function getName(): string
    {
        return 'scale';
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(?Color $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getColorMin(): Color
    {
        return $this->colorMin;
    }

    public function setColorMin(Color $colorMin): static
    {
        $this->colorMin = $colorMin;

        return $this;
    }

    public function getColorMax(): Color
    {
        return $this->colorMax;
    }

    public function setColorMax(Color $colorMax): static
    {
        $this->colorMax = $colorMax;

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

    public function getValue(): float|int|null
    {
        return $this->value;
    }

    public function setValue(float|int|null $value): static
    {
        $this->value = $value;

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
