<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use DateTimeInterface;
use Spyck\VisualizationBundle\Controller\WidgetController;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Aggregate
{
    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private array|bool|DateTimeInterface|float|int|string|null $min = null;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private array|bool|DateTimeInterface|float|int|string|null $max = null;

    public function getMin(): array|bool|DateTimeInterface|float|int|string|null
    {
        return $this->min;
    }

    public function setMin(array|bool|DateTimeInterface|float|int|string|null $min): static
    {
        $this->min = $min;

        return $this;
    }

    public function getMax(): array|bool|DateTimeInterface|float|int|string|null
    {
        return $this->max;
    }

    public function setMax(array|bool|DateTimeInterface|float|int|string|null $max): static
    {
        $this->max = $max;

        return $this;
    }
}
