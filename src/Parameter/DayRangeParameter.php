<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use Spyck\VisualizationBundle\Request\AbstractMultipleRequest;

final class DayRangeParameter extends AbstractMultipleRequest
{
    public function __construct()
    {
        $this
            ->addChild(new DayStartParameter())
            ->addChild(new DayEndParameter());
    }
}
