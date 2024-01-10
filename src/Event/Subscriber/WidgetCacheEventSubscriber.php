<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event\Subscriber;

use Exception;
use Psr\Log\LoggerInterface;
use Spyck\VisualizationBundle\Entity\Log;
use Spyck\VisualizationBundle\Event\WidgetCacheEvent;
use Spyck\VisualizationBundle\Message\MailMessageInterface;
use Spyck\VisualizationBundle\Repository\LogRepository;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Spyck\VisualizationBundle\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[AutoconfigureTag('monolog.logger', ['channel' => 'spyck_visualization'])]
final class WidgetCacheEventSubscriber implements EventSubscriberInterface
{
    public function __construct(#[Autowire(service: 'spyck.visualization.config.cache.adapter')] private readonly CacheInterface $cache, private readonly LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WidgetCacheEvent::class => [
                'onWidgetCache',
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function onWidgetCache(WidgetCacheEvent $event): void
    {
        if (false === $this->cache instanceof TagAwareCacheInterface) {
            $this->logger->error(sprintf('Cache is not instance of "%s"', TagAwareCacheInterface::class));

            return;
        }

        $this->cache->invalidateTags([
            sprintf('spyck_visualization_widget_%s', $event->getWidget()->getId()),
        ]);
    }
}
