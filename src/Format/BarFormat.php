<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Format;

use Spyck\ApiExtension\Model\Response;
use Symfony\Component\Serializer\Annotation as Serializer;

final class BarFormat implements FormatInterface
{
    #[Serializer\Groups(groups: Response::GROUP)]
    private string $color;

    public function __construct(string $color)
    {
        $this->setColor($color);
    }

    #[Serializer\Groups(groups: Response::GROUP)]
    public function getName(): string
    {
        return 'databar';
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
