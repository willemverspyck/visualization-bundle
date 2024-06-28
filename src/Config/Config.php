<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Config;

use Spyck\ApiExtension\Model\Response;
use Spyck\VisualizationBundle\Controller\WidgetController;
use Spyck\VisualizationBundle\Field\FieldInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Config
{
    private FieldInterface $field;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private ?bool $abbreviation = null;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private ?int $precision = null;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private bool $merge = false;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private ?array $chart = null;

    public function getField(): FieldInterface
    {
        return $this->field;
    }

    public function setField(FieldInterface $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function hasAbbreviation(): ?bool
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(?bool $abbreviation): static
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function setPrecision(?int $precision): static
    {
        $this->precision = $precision;

        return $this;
    }

    public function hasMerge(): bool
    {
        return $this->merge;
    }

    public function setMerge(bool $merge): static
    {
        $this->merge = $merge;

        return $this;
    }

    public function getChart(): ?array
    {
        return $this->chart;
    }

    public function setChart(?array $chart): static
    {
        $this->chart = $chart;

        return $this;
    }
}
