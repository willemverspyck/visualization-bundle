<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event;

use Spyck\VisualizationBundle\Model\Dashboard;
use Symfony\Contracts\EventDispatcher\Event;

final class DashboardEvent extends Event
{
    public function __construct(private readonly Dashboard $dashboard)
    {
    }

    public function getDashboard(): Dashboard
    {
        return $this->dashboard;
    }
}
