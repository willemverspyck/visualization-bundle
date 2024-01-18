<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use Spyck\VisualizationBundle\Request\AbstractMultipleRequest;

final class MonthRangeParameter extends AbstractMultipleRequest
{
    public function __construct()
    {
        $monthStartParameter = new MonthStartParameter();
        $monthStartParameter->setParent($this);

        $this->addChild($monthStartParameter);

        $monthEndParameter = new MonthEndParameter();
        $monthEndParameter->setParent($this);

        $this->addChild($monthEndParameter);
    }
}
