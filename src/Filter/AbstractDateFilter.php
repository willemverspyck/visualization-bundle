<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use Spyck\VisualizationBundle\Utility\DateTimeUtility;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractDateFilter extends AbstractFilter implements DateFilterInterface
{
    public function getData(): ?array
    {
        return array_map(function (DateTimeInterface $date): string {
            return $date->format(DateTimeUtility::FORMAT_DATE);
        }, $this->data);
    }

    public function getDataAsObject(): ?array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = array_map(function (string $date): DateTimeInterface {
            try {
                return new DateTimeImmutable($date);
            } catch (DateMalformedStringException) {
                throw new NotFoundHttpException(sprintf('Filter "%s" with "%s" is invalid', $this->getName(), $date));
            }
        }, $data);
    }

    public function getType(): string
    {
        return FilterInterface::TYPE_DATE;
    }
}
