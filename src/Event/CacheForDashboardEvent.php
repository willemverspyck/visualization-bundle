<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event;

use Spyck\VisualizationBundle\Entity\Dashboard;
use Symfony\Contracts\EventDispatcher\Event;

final class CacheForDashboardEvent extends Event
{
    public function __construct(private readonly Dashboard $dashboard)
    {
    }

    public function getDashboard(): Dashboard
    {
        return $this->dashboard;
    }
}
