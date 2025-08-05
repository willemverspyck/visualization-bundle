<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event;

use Spyck\VisualizationBundle\Model\Block;
use Symfony\Contracts\EventDispatcher\Event;

final class BlockEvent extends Event
{
    public function __construct(private readonly Block $block)
    {
    }

    public function getBlock(): Block
    {
        return $this->block;
    }
}
