<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use DateTimeInterface;

interface TimestampInterface
{
    public function getTimestampCreated(): DateTimeInterface;

    public function setTimestampCreated(DateTimeInterface $timestampCreated): self;

    public function getTimestampUpdated(): ?DateTimeInterface;

    public function setTimestampUpdated(DateTimeInterface $timestampUpdated): self;
}
