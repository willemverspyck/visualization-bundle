<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Field;

use Doctrine\Common\Collections\Collection;
use Spyck\VisualizationBundle\Format\FormatInterface;
use Spyck\VisualizationBundle\Model\Aggregate;

interface AbstractFieldInterface
{
    public function getName(): string;

    public function setName(string $name): static;

    public function getAggregate(): ?Aggregate;

    public function setAggregate(?Aggregate $aggregate): static;

    public function isActive(): bool;

    public function setActive(bool $active): static;

    public function addFormat(FormatInterface $format): static;

    public function getFormats(): Collection;
}
