<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class WeekStartParameter extends AbstractDateParameter
{
    public function getField(): string
    {
        return RequestInterface::DATE_START;
    }

    public static function getName(): string
    {
        return RequestInterface::DATE_START;
    }

    public function getDataForQueryBuilder(): ?string
    {
        $data = $this->getData();

        return $data?->format('Y-m-d');
    }
}
