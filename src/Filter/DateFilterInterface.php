<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

interface DateFilterInterface extends FilterInterface
{
    public function getDataAsObject(): ?array;
}
