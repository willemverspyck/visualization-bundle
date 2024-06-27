<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Field;

use Doctrine\Common\Collections\Collection;
use Spyck\VisualizationBundle\Format\FormatInterface;

interface MultipleFieldInterface
{
    public function getName(): string;

    public function setName(string $name): static;

    public function addChild(FieldInterface $child): static;

    public function getChildren(): Collection;

    public function removeChild(FieldInterface $child): void;

    public function addFormat(FormatInterface $format): static;

    public function getFormats(): Collection;
}
