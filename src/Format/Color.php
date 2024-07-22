<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Format;

use Spyck\VisualizationBundle\Controller\WidgetController;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Color
{
    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private int $red;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private int $green;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private int $blue;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private float $alpha;

    public function __construct(int $red, int $green, int $blue, float $alpha = 1)
    {
        $this->setRed($red);
        $this->setGreen($green);
        $this->setBlue($blue);
        $this->setAlpha($alpha);
    }

    public function getRed(): int
    {
        return $this->red;
    }

    public function setRed(int $red): static
    {
        $this->red = $red;

        return $this;
    }

    public function getGreen(): int
    {
        return $this->green;
    }

    public function setGreen(int $green): static
    {
        $this->green = $green;

        return $this;
    }

    public function getBlue(): int
    {
        return $this->blue;
    }

    public function setBlue(int $blue): static
    {
        $this->blue = $blue;

        return $this;
    }

    public function getAlpha(): float
    {
        return $this->alpha;
    }

    public function setAlpha(float $alpha): static
    {
        $this->alpha = $alpha;

        return $this;
    }

    public function getHex(): string
    {
        return sprintf('%02x%02x%02x', $this->getRed(), $this->getGreen(), $this->getBlue());
    }
}
