<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Format;

use Spyck\VisualizationBundle\Controller\WidgetController;
use Symfony\Component\Serializer\Annotation as Serializer;

final class ScaleFormat implements FormatInterface
{
    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private ?string $color = null;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private string $colorMin;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private string $colorMax;

    public function __construct(?string $color, string $colorMin, string $colorMax)
    {
        $this->setColor($color);
        $this->setColorMin($colorMin);
        $this->setColorMax($colorMax);
    }

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    public function getName(): string
    {
        return 'scale';
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getColorMin(): string
    {
        return $this->colorMin;
    }

    public function setColorMin(string $colorMin): static
    {
        $this->colorMin = $colorMin;

        return $this;
    }

    public function getColorMax(): string
    {
        return $this->colorMax;
    }

    public function setColorMax(string $colorMax): static
    {
        $this->colorMax = $colorMax;

        return $this;
    }
}
