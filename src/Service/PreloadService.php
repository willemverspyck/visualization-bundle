<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Spyck\VisualizationBundle\Entity\Preload;
use Spyck\VisualizationBundle\Entity\ScheduleInterface;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Spyck\VisualizationBundle\Message\PreloadMessage;
use Spyck\VisualizationBundle\Repository\PreloadRepository;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class PreloadService
{
    public function __construct(private PreloadRepository $preloadRepository, private MessageBusInterface $messageBus)
    {
    }

    public function executePreload(Preload $preload): void
    {
        $users = $preload->getUsers();

        if ($users->isEmpty()) {
            $this->executePreloadAsMessage($preload);

            return;
        }

        foreach ($users as $user) {
            $this->executePreloadAsMessage($preload, $user);
        }
    }

    public function executePreloadAsMessage(Preload $preload, ?UserInterface $user = null, array $stamps = []): void
    {
        $preloadMessage = new PreloadMessage();
        $preloadMessage->setDashboardId($preload->getDashboard()->getId());
        $preloadMessage->setUserId(null === $user ? null : $user->getId());
        $preloadMessage->setVariables($preload->getVariables());

        $this->messageBus->dispatch($preloadMessage, $stamps);
    }

    public function executePreloadAsMessageBySchedule(ScheduleInterface $schedule): void
    {
        $preloads = $this->preloadRepository->getPreloadsBySchedule($schedule);

        foreach ($preloads as $preload) {
            $this->executePreload($preload);
        }
    }
}
