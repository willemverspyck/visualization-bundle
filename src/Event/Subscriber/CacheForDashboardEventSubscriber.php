<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event\Subscriber;

use Exception;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Spyck\VisualizationBundle\Event\CacheForDashboardEvent;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[AutoconfigureTag('monolog.logger', ['channel' => 'spyck_visualization'])]
final class CacheForDashboardEventSubscriber implements EventSubscriberInterface
{
    public function __construct(#[Autowire(service: 'spyck.visualization.config.cache.adapter')] private readonly CacheInterface $cache, private readonly DashboardRepository $dashboardRepository, private readonly LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CacheForDashboardEvent::class => [
                'onCacheForDashboard',
            ],
        ];
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function onCacheForDashboard(CacheForDashboardEvent $event): void
    {
        if (false === $this->cache instanceof TagAwareCacheInterface) {
            $this->logger->error(sprintf('Cache is not instance of "%s"', TagAwareCacheInterface::class));

            return;
        }

        $dashboard = $this->dashboardRepository->getDashboardById($event->getDashboard()->getId(), false);

        foreach ($dashboard->getBlocks() as $block) {
            $this->cache->invalidateTags([
                sprintf('spyck_visualization_widget_%s', $block->getWidget()->getId()),
            ]);
        }
    }
}
