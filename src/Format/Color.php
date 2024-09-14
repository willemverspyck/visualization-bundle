<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Format;

use Spyck\VisualizationBundle\Controller\WidgetController;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Color
{
    private const float ALPHA = 1;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private int $red;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private int $green;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private int $blue;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private float $alpha;

    public function __construct(int $red, int $green, int $blue, float $alpha = self::ALPHA)
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

    public function getCodeAsHex(bool $hash = false): string
    {
        return sprintf('%s%02x%02x%02x', $hash ? '#' : '', $this->getRed(), $this->getGreen(), $this->getBlue());
    }

    public function getCodeAsRgb(): string
    {
        if (self::ALPHA === $this->getAlpha()) {
            return sprintf('rgb(%d, %d, %d)', $this->getRed(), $this->getGreen(), $this->getBlue());
        }

        return sprintf('rgba(%d, %d, %d, %0.2f)', $this->getRed(), $this->getGreen(), $this->getBlue(), $this->getAlpha());
    }
}
