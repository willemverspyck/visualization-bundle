<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use Spyck\VisualizationBundle\Request\AbstractMultipleRequest;

final class WeekRangeParameter extends AbstractMultipleRequest
{
    public function __construct(int $weekday = DateParameterInterface::MONDAY)
    {
        $weekStartParameter = new WeekStartParameter($weekday);
        $weekStartParameter->setParent($this);

        $this->addChild($weekStartParameter);

        $weekEndParameter = new WeekEndParameter($weekday);
        $weekEndParameter->setParent($this);

        $this->addChild($weekEndParameter);
    }
}
