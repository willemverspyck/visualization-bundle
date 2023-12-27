<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;

trait TimestampTrait
{
    #[Doctrine\Column(name: 'timestamp_created', type: Types::DATETIME_MUTABLE)]
    protected DateTimeInterface $timestampCreated;

    #[Doctrine\Column(name: 'timestamp_updated', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $timestampUpdated = null;

    public function getTimestampCreated(): DateTimeInterface
    {
        return $this->timestampCreated;
    }

    public function setTimestampCreated(DateTimeInterface $timestampCreated): self
    {
        $this->timestampCreated = $timestampCreated;

        return $this;
    }

    public function getTimestampUpdated(): ?DateTimeInterface
    {
        return $this->timestampUpdated;
    }

    public function setTimestampUpdated(DateTimeInterface $timestampUpdated): self
    {
        $this->timestampUpdated = $timestampUpdated;

        return $this;
    }
}
