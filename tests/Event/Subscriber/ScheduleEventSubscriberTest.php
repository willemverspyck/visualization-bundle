<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Tests\Event\Subscriber;

use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spyck\VisualizationBundle\Entity\Schedule;
use Spyck\VisualizationBundle\Entity\ScheduleInterface;
use Spyck\VisualizationBundle\Event\ScheduleEvent;
use Spyck\VisualizationBundle\Event\Subscriber\ScheduleEventSubscriber;
use Spyck\VisualizationBundle\Service\MailService;
use Spyck\VisualizationBundle\Service\PreloadService;

final class ScheduleEventSubscriberTest extends TestCase
{
    private MockObject $mailService;
    private MockObject $preloadService;
    private ScheduleEventSubscriber $scheduleEventSubscriber;

    protected function setUp(): void
    {
        $this->mailService = $this->createMock(MailService::class);
        $this->preloadService = $this->createMock(PreloadService::class);

        $this->scheduleEventSubscriber = new ScheduleEventSubscriber($this->mailService, $this->preloadService);
    }

    /**
     * @dataProvider provideScheduleData
     */
    public function testOnScheduleWithDataProvider(array $hours, array $days, array $weeks, array $weekdays, DateTimeImmutable $date, bool $execute): void
    {
        // Create a mock Schedule
        $schedule = $this->createMock(ScheduleInterface::class);
        $schedule->method('getHours')->willReturn($hours);
        $schedule->method('getDays')->willReturn($days);
        $schedule->method('getWeeks')->willReturn($weeks);
        $schedule->method('getWeekdays')->willReturn($weekdays);

        // Create a mock ScheduleEvent
        $event = $this->createMock(ScheduleEvent::class);
        $event->method('getDate')->willReturn($date);
        $event->method('getSchedule')->willReturn($schedule);

        // Set up expectations for MailService and PreloadService
        $this->mailService->expects($execute ? $this->once() : $this->never())
            ->method('executeMailMessageBySchedule')
            ->with($schedule);

        $this->preloadService->expects($execute ? $this->once() : $this->never())
            ->method('executePreloadMessageBySchedule')
            ->with($schedule);

        // Call the onSchedule method
        $this->scheduleEventSubscriber->onSchedule($event);

        $this->assertTrue(true, 'Ensure at least one assertion is made.');
    }

    public function provideScheduleData(): array
    {
        return [
            // Case where the date matches the schedule (should execute)
            [
                'hours' => [10],
                'days' => [15],
                'weeks' => [41],
                'weekdays' => [7],
                'date' => new DateTimeImmutable('2023-10-15 10:00:00'), // Matches schedule
                'execute' => true,
            ],
            // Case where the date matches the schedule (should execute)
            [
                'hours' => [],
                'days' => [],
                'weeks' => [],
                'weekdays' => [],
                'date' => new DateTimeImmutable('2023-10-15 10:00:00'), // Matches schedule
                'execute' => true,
            ],
            // Case where hour does not match (should not execute)
            [
                'hours' => [9], // Does not match hour
                'days' => [15],
                'weeks' => [41],
                'weekdays' => [7],
                'date' => new DateTimeImmutable('2023-10-15 10:00:00'),
                'execute' => false,
            ],
            // Case where day does not match (should not execute)
            [
                'hours' => [10],
                'days' => [14], // Does not match day
                'weeks' => [41],
                'weekdays' => [7],
                'date' => new DateTimeImmutable('2023-10-15 10:00:00'),
                'execute' => false,
            ],
            // Case where week does not match (should not execute)
            [
                'hours' => [10],
                'days' => [15],
                'weeks' => [40], // Does not match week
                'weekdays' => [7],
                'date' => new DateTimeImmutable('2023-10-15 10:00:00'),
                'execute' => false,
            ],
            // Case where weekday does not match (should not execute)
            [
                'hours' => [10],
                'days' => [15],
                'weeks' => [41],
                'weekdays' => [6], // Does not match weekday (N=3)
                'date' => new DateTimeImmutable('2023-10-15 10:00:00'),
                'execute' => false,
            ],
        ];
    }
}
