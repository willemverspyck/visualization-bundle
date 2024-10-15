<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event\Subscriber;

use Spyck\VisualizationBundle\Event\ScheduleEvent;
use Spyck\VisualizationBundle\Service\MailService;
use Spyck\VisualizationBundle\Service\PreloadService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

#[AutoconfigureTag('monolog.logger', ['channel' => 'spyck_visualization'])]
final class ScheduleEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MailService $mailService, private readonly PreloadService $preloadService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScheduleEvent::class => [
                'onSchedule',
            ],
        ];
    }

    public function onSchedule(ScheduleEvent $event): void
    {
        $date = $event->getDate();
        $schedule = $event->getSchedule();

        if (false === $this->match($schedule->getMatchHours(), (int) $date->format('G'))) {
            return;
        }

        if (false === $this->match($schedule->getMatchDays(), (int) $date->format('j'))) {
            return;
        }

        if (false === $this->match($schedule->getMatchWeeks(), (int) $date->format('W'))) {
            return;
        }

        if (false === $this->match($schedule->getMatchWeekdays(), (int) $date->format('N'))) {
            return;
        }

        $this->mailService->executeMailMessageBySchedule($schedule);
        $this->preloadService->executePreloadMessageBySchedule($schedule);
    }

    private function match(array $data, int $value): bool
    {
        return 0 === count($data) || in_array($value, $data, true);
    }
}
