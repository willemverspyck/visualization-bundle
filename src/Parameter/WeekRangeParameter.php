<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use Spyck\VisualizationBundle\Request\AbstractMultipleRequest;

final class WeekRangeParameter extends AbstractMultipleRequest
{
    public function __construct(bool $full = false)
    {
        $weekStartParameter = new WeekStartParameter();
        $weekStartParameter->setParent($this);

        $this->addChild($weekStartParameter);

        $weekEndParameter = new WeekEndParameter();
        $weekEndParameter->setParent($this);

        $this->addChild($weekEndParameter);
    }
}
