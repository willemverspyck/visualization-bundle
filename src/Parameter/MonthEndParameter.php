<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use DateTimeImmutable;
use Spyck\VisualizationBundle\Request\RequestInterface;

final class MonthEndParameter extends AbstractDateParameter
{
    public function getData(): ?DateTimeImmutable
    {
        $data = parent::getData();

        if (null === $data) {
            return null;
        }

        return $data->modify('Last day of this month');
    }

    public static function getField(): string
    {
        return RequestInterface::DATE_END;
    }

    public static function getName(): string
    {
        return RequestInterface::DATE_END;
    }
}
