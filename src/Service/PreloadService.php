<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Spyck\VisualizationBundle\Entity\Preload;
use Spyck\VisualizationBundle\Entity\ScheduleInterface;
use Spyck\VisualizationBundle\Message\PreloadMessage;
use Spyck\VisualizationBundle\Repository\PreloadRepository;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class PreloadService
{
    public function __construct(private PreloadRepository $preloadRepository, private MessageBusInterface $messageBus)
    {
    }

    public function executePreloadAsMessage(Preload $preload): void
    {
        $preloadMessage = new PreloadMessage();
        $preloadMessage->setDashboardId($preload->getDashboard()->getId());
        $preloadMessage->setVariables($preload->getVariables());

        $this->messageBus->dispatch($preloadMessage);
    }

    public function executePreloadAsMessageBySchedule(ScheduleInterface $schedule): void
    {
        $preloads = $this->preloadRepository->getPreloadsBySchedule($schedule);

        foreach ($preloads as $preload) {
            $this->executePreloadAsMessage($preload);
        }
    }
}
