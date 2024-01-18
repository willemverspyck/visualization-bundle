<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use DateTime;
use DateTimeInterface;
use Exception;
use Spyck\VisualizationBundle\Utility\DateTimeUtility;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractDateParameter extends AbstractParameter implements DateParameterInterface
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
        try {
            $this->data = new DateTime($data);
        } catch (Exception) {
            throw new NotFoundHttpException(sprintf('Parameter "%s" with "%s" is invalid', $this->getName(), $data));
        }
    }
}
