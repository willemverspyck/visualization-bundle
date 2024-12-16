<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use DateTimeImmutable;
use Spyck\VisualizationBundle\Request\RequestInterface;

final class MonthStartParameter extends AbstractDateParameter
{
    public function getData(): ?DateTimeImmutable
    {
        $data = parent::getData();

        if (null === $data) {
            return null;
        }

        return $data->modify('First day of this month');
    }

    public static function getField(): string
    {
        return RequestInterface::DATE_START;
    }

    public static function getName(): string
    {
        return RequestInterface::DATE_START;
    }
}
