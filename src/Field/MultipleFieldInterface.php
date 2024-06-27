<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Field;

use Doctrine\Common\Collections\Collection;
use Spyck\VisualizationBundle\Format\FormatInterface;

interface MultipleFieldInterface extends AbstractFieldInterface
{
    public function addChild(FieldInterface $child): static;

    public function getChildren(): Collection;
}
