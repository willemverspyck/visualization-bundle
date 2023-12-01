<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use DateTime;
use DateTimeInterface;
use Exception;
use Spyck\VisualizationBundle\Utility\DateTimeUtility;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractDateParameter implements DateParameterInterface
{
    private ?DateTimeInterface $data = null;

    public function getData(): ?DateTimeInterface
    {
        return $this->data;
    }

    public function getDataAsString(bool $slug = false): ?string
    {
        $data = $this->getData();

        return $data?->format($slug ? 'Ymd' : 'Y-m-d');
    }

    public function getDataForQueryBuilder(): ?string
    {
        $data = $this->getData();

        return $data?->format('Y-m-d');
    }

    public function getDataForRequest(): ?string
    {
        $data = $this->getData();

        return $data?->format(DateTimeUtility::FORMAT_DATE);
    }

    /**
     * @throws Exception
     */
    public function setData(string $data): void
    {
        $data = DateTime::createFromFormat(DateTimeUtility::FORMAT_DATE, $data);

        if (false === $data) {
            throw new NotFoundHttpException();
        }

        $this->data = $data;
    }
}
