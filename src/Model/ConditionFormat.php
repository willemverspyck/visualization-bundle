<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Spyck\ApiExtension\Model\Response;
use Symfony\Component\Serializer\Annotation as Serializer;

final class ConditionFormat implements FormatInterface
{
    #[Serializer\Groups(groups: Response::GROUP)]
    private ?float $start = null;

    #[Serializer\Groups(groups: Response::GROUP)]
    private ?float $end = null;

    #[Serializer\Groups(groups: Response::GROUP)]
    private string $color;

    public function __construct(?float $start, ?float $end, string $color)
    {
        $this->setStart($start);
        $this->setEnd($end);
        $this->setColor($color);
    }

    public function getStart(): ?float
    {
        return $this->start;
    }

    public function setStart(?float $start): static
    {
        $this->start = $start;

        return $this;
    }
    public function getEnd(): ?float
    {
        return $this->end;
    }

    public function setEnd(?float $end): static
    {
        $this->end = $end;

        return $this;
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
}
