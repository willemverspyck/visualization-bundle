<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event\Subscriber;

use Spyck\VisualizationBundle\Event\PreloadEvent;
use Spyck\VisualizationBundle\Service\PreloadService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PreloadEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly PreloadService $preloadService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreloadEvent::class => [
                'onPreload',
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function onPreload(PreloadEvent $event): void
    {
        $this->preloadService->executePreloadAsMessage($event->getPreload());
    }
}
