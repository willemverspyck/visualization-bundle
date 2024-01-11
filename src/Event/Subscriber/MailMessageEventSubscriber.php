<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event\Subscriber;

use Exception;
use Spyck\VisualizationBundle\Entity\Log;
use Spyck\VisualizationBundle\Message\MailMessageInterface;
use Spyck\VisualizationBundle\Repository\LogRepository;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Spyck\VisualizationBundle\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Throwable;

final class MailMessageEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly DashboardRepository $dashboardRepository, private readonly LogRepository $logRepository, private readonly UserRepository $userRepository)
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => [
                'onWorkerMessageFailed',
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function onWorkerMessageFailed(WorkerMessageFailedEvent $event): void
    {
        if ($event->willRetry()) {
            return;
        }

        $message = $event->getEnvelope()->getMessage();

        if ($message instanceof MailMessageInterface) {
            $user = $this->userRepository->getUserById($message->getUser());

            if (null === $user) {
                return;
            }

            $dashboard = $this->dashboardRepository->getDashboardById($message->getId(), false);

            if (null === $dashboard) {
                return;
            }

            $messages = $this->getMessages($event->getThrowable());

            $this->logRepository->putLog(user: $user, dashboard: $dashboard, variables: $message->getVariables(), view: $message->getView(), type: Log::TYPE_MAIL, messages: $messages);
        }
    }

    private function getMessages(Throwable $throwable): array
    {
        $data = [
            $throwable->getMessage(),
        ];

        $previous = $throwable->getPrevious();

        if (null === $previous) {
            return $data;
        }

        return array_merge($data, $this->getMessages($previous));
    }
}
