<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class Config
{
    private Field $field;
    private bool $abbreviation = false;
    private ?int $precision = null;
    private bool $merge = false;
    private ?array $chart = null;
    private Collection $formats;

    public function __construct()
    {
        $this->formats = new ArrayCollection();
    }

    public function getField(): Field
    {
        return $this->field;
    }

    public function setField(Field $field): static
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

    /**
     * @return Collection<int, FormatInterface>
     */
    public function getFormats(): Collection
    {
        return $this->formats;
    }

    public function addFormat(FormatInterface $format): static
    {
        $this->formats->add($format);

        return $this;
    }

    public function toArray(): array
    {
        $data = [
            'merge' => $this->hasMerge(),
            'chart' => $this->getChart(),
        ];

        switch ($this->getField()->getType()) {
            case Field::TYPE_CURRENCY:
            case Field::TYPE_NUMBER:
                $data['abbreviation'] = $this->hasAbbreviation();

                break;
            case Field::TYPE_PERCENTAGE:
            case Field::TYPE_POSITION:
                $data['abbreviation'] = false;

                break;
        }

        switch ($this->getField()->getType()) {
            case Field::TYPE_CURRENCY:
            case Field::TYPE_NUMBER:
            case Field::TYPE_PERCENTAGE:
                $formats = [];

                foreach ($this->getFormats() as $format) {
                    $formats[] = $format->toArray();
                }

                $data['formats'] = $formats;
                $data['precision'] = $this->getPrecision();

                break;
        }

        return $data;
    }
}
