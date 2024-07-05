<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Spyck\VisualizationBundle\Controller\WidgetController;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Aggregate
{
    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private float|int|null $min = null;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private float|int|null $max = null;

    public function getMin(): float|int|null
    {
        return $this->min;
    }

    public function setMin(float|int|null $min): static
    {
        $this->min = $min;

        return $this;
    }

    public function getMax(): float|int|null
    {
        return $this->max;
    }

    public function setMax(float|int|null $max): static
    {
        $this->max = $max;

        return $this;
    }
}
