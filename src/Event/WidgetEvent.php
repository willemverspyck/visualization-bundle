<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event;

use Spyck\VisualizationBundle\Model\Widget;
use Symfony\Contracts\EventDispatcher\Event;

final class WidgetEvent extends Event
{
    public function __construct(private readonly Widget $widget)
    {
    }

    public function getWidget(): Widget
    {
        return $this->widget;
    }
}
