<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class MonthEndParameter extends AbstractDateParameter
{
    public function getField(): string
    {
        return RequestInterface::DATE_END;
    }

    public static function getName(): string
    {
        return RequestInterface::DATE_END;
    }

    public function getDataForQueryBuilder(): ?string
    {
        $data = $this->getData();

        return $data?->format('Ym');
    }
}
