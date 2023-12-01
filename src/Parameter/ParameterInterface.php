<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use Spyck\VisualizationBundle\Request\RequestInterface;

interface ParameterInterface extends RequestInterface
{
    public function getDataAsString(bool $slug = false): ?string;

    public function setData(string $data): void;
}
