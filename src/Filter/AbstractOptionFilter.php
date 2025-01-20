<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

abstract class AbstractOptionFilter extends AbstractFilter implements OptionFilterInterface
{
    private array $options = [];
    private bool $multiple = true;

    public function getConfig(): ?array
    {
        $config = parent::getConfig();
        $config['multiple'] = $this->isMultiple();

        return $config;
    }

    public function getDataAsOptions(): ?array
    {
        $data = $this->getData();

        if (null === $data) {
            return null;
        }

        $intersect = array_values(array_intersect_key($this->getOptions(), array_flip($data)));

        if (count($intersect) > 0) {
            return $intersect;
        }

        return null;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function setMultiple(bool $multiple): void
    {
        $this->multiple = $multiple;
    }

    public function getType(): string
    {
        return $this->isMultiple() ? FilterInterface::TYPE_CHECKBOX : FilterInterface::TYPE_SELECT;
    }
}
