<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Context;

use Spyck\VisualizationBundle\View\ViewInterface;

final class ExcelContext extends AbstractContext implements ContextInterface
{
    private ?int $width = null;

    public function __construct()
    {
        $this->setView(ViewInterface::XLSX);
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): static
    {
        $this->width = $width;

        return $this;
    }
}
