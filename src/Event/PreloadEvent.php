<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event;

use Spyck\VisualizationBundle\Entity\Preload;
use Symfony\Contracts\EventDispatcher\Event;

final class PreloadEvent extends Event
{
    public function __construct(private readonly Preload $preload)
    {
    }

    public function getPreload(): Preload
    {
        return $this->preload;
    }
}
