<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Context;

final class ExcelContext extends AbstractContext
{
    private ?int $width = null;
    private bool $visible = true;

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;

        return $this;
    }
}
