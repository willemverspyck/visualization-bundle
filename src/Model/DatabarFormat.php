<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Spyck\ApiExtension\Model\Response;
use Symfony\Component\Serializer\Annotation as Serializer;

final class DatabarFormat implements FormatInterface
{
    #[Serializer\Groups(groups: Response::GROUP)]
    private string $color;

    public function __construct(string $color)
    {
        $this->setColor($color);
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

    public function toArray(): array
    {
        return [
            'type' => 'databar',
            'color' => $this->getColor(),
        ];
    }
}
