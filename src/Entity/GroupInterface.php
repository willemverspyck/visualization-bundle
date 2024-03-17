<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

interface GroupInterface
{
    public function getId(): ?int;

    public function isActive(): bool;
}
