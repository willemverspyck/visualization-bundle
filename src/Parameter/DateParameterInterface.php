<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use DateTimeImmutable;

interface DateParameterInterface extends ParameterInterface
{
    public function getData(): ?DateTimeImmutable;

    public function getDataForQueryBuilder(): ?string;

    public function getDataForRequest(): ?string;
}
