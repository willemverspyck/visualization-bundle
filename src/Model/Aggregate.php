<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use DateTimeInterface;
use Spyck\VisualizationBundle\Controller\WidgetController;
use Symfony\Component\Serializer\Attribute as Serializer;

final class Aggregate
{
    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private DateTimeInterface|float|int|null $min = null;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private DateTimeInterface|float|int|null $max = null;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private DateTimeInterface|float|int|null $median = null;

    public function getMin(): DateTimeInterface|float|int|null
    {
        return $this->min;
    }

    public function setMin(DateTimeInterface|float|int|null $min): static
    {
        $this->min = $min;

        return $this;
    }

    public function getMax(): DateTimeInterface|float|int|null
    {
        return $this->max;
    }

    public function setMax(DateTimeInterface|float|int|null $max): static
    {
        $this->max = $max;

        return $this;
    }

    public function getMedian(): DateTimeInterface|float|int|null
    {
        return $this->median;
    }

    public function setMedian(DateTimeInterface|float|int|null $median): static
    {
        $this->median = $median;

        return $this;
    }
}
