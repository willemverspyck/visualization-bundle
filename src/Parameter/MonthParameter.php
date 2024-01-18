<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class MonthParameter extends AbstractDateParameter
{
    public static function getField(): string
    {
        return RequestInterface::DATE;
    }

    public static function getName(): string
    {
        return RequestInterface::DATE;
    }

    public function getDataForQueryBuilder(): ?string
    {
        $data = $this->getData();

        return $data?->format('Ym');
    }
}
