<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use Doctrine\ORM\Mapping as Doctrine;

#[Doctrine\Entity]
class ScheduleForEvent extends AbstractSchedule
{
    public function getDiscriminator(): string
    {
        return 'Event';
    }
}
