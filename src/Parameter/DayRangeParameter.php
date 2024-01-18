<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use Spyck\VisualizationBundle\Request\AbstractMultipleRequest;

final class DayRangeParameter extends AbstractMultipleRequest
{
    public function __construct()
    {
        $dayStartParameter = new DayStartParameter();
        $dayStartParameter->setParent($this);

        $this->addChild($dayStartParameter);

        $dayEndParameter = new DayEndParameter();
        $dayEndParameter->setParent($this);

        $this->addChild($dayEndParameter);
    }
}
