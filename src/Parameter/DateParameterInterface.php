<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use DateTimeImmutable;

interface DateParameterInterface extends ParameterInterface
{
    public const int SUNDAY = 0;
    public const int MONDAY = 1;

    public function getData(): ?DateTimeImmutable;

    public function getDataForQueryBuilder(): ?string;

    public function getDataForRequest(): ?string;
}
