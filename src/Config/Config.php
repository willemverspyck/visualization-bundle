<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Spyck\ApiExtension\Model\Response;
use Spyck\VisualizationBundle\Field\FieldInterface;

final class Config
{
    private FieldInterface $field;
    #[Serializer\Groups(groups: Response::GROUP)]
    private bool $abbreviation = false;
    #[Serializer\Groups(groups: Response::GROUP)]
    private ?int $precision = null;
    #[Serializer\Groups(groups: Response::GROUP)]
    private bool $merge = false;

    #[Serializer\Groups(groups: Response::GROUP)]
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

    public function hasAbbreviation(): bool
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(bool $abbreviation): static
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

    public function toArray(): array
    {
        $data = [
            'chart' => $this->getChart(),
            'merge' => $this->hasMerge(),
        ];

        switch ($this->getField()->getType()) {
            case FieldInterface::TYPE_CURRENCY:
            case FieldInterface::TYPE_NUMBER:
                $data['abbreviation'] = $this->hasAbbreviation();

                break;
            case FieldInterface::TYPE_PERCENTAGE:
            case FieldInterface::TYPE_POSITION:
                $data['abbreviation'] = false;

                break;
        }

        switch ($this->getField()->getType()) {
            case FieldInterface::TYPE_CURRENCY:
            case FieldInterface::TYPE_NUMBER:
            case FieldInterface::TYPE_PERCENTAGE:
                $data['precision'] = $this->getPrecision();

                break;
        }

        return $data;
    }
}
