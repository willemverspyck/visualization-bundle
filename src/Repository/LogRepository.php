<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Log;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Entity\UserInterface;

class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Log::class);
    }

    public function putLog(?UserInterface $user, Dashboard $dashboard, array $variables, ?string $view, int $type, array $messages = null): Log
    {
        $log = new Log();
        $log->setUser($user);
        $log->setDashboard($dashboard);
        $log->setVariables($variables);
        $log->setView($view);
        $log->setType($type);
        $log->setMessages($messages);

        $this->getEntityManager()->persist($log);
        $this->getEntityManager()->flush();

        return $log;
    }
}
