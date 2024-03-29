<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class WeekEndParameter extends AbstractDateParameter
{
    public function __construct(private readonly bool $full = false)
    {
    }

    public static function getField(): string
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

        if (null === $data) {
            return null;
        }

        if ($this->full) {
            return $data->modify('Last Sunday')->format('Y-m-d');
        }

        return $data->format('Y-m-d');
    }
}
