<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use DateTimeInterface;

interface DateParameterInterface extends ParameterInterface
{
    public function getData(): ?DateTimeInterface;

    public function getDataForQueryBuilder(): ?string;

    public function getDataForRequest(): ?string;
}
