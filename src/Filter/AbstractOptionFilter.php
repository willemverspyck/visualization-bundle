<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

abstract class AbstractOptionFilter extends AbstractFilter implements OptionFilterInterface
{
    private array $options = [];

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
}
