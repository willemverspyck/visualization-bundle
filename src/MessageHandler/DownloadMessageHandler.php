<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\MessageHandler;

use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Spyck\VisualizationBundle\Entity\Download;
use Spyck\VisualizationBundle\Event\DownloadEvent;
use Spyck\VisualizationBundle\Message\DownloadMessageInterface;
use Spyck\VisualizationBundle\Repository\DownloadRepository;
use Spyck\VisualizationBundle\Service\DownloadService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

#[AsMessageHandler]
final readonly class DownloadMessageHandler
{
    public function __construct(private DownloadRepository $downloadRepository, private DownloadService $downloadService, private EventDispatcherInterface $eventDispatcher, private TokenStorageInterface $tokenStorage)
    {
    }

    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function __invoke(DownloadMessageInterface $downloadMessage): void
    {
        $download = $this->getDownloadById($downloadMessage->getId());
        $user = $download->getUser();

        $token = $this->tokenStorage->getToken();

        $usernamePasswordToken = new UsernamePasswordToken($user, get_class($this), $user->getRoles());

        $this->tokenStorage->setToken($usernamePasswordToken);

        $this->downloadService->executeDownload($download);

        $this->tokenStorage->setToken($token);

        $downloadEvent = new DownloadEvent($download);

        $this->eventDispatcher->dispatch($downloadEvent);
    }

    /**
     * Check if the download exists.
     *
     * @throws NonUniqueResultException
     * @throws UnrecoverableMessageHandlingException
     */
    private function getDownloadById(int $id): Download
    {
        $download = $this->downloadRepository->getDownloadById($id);

        if (null === $download) {
            throw new UnrecoverableMessageHandlingException(sprintf('Download not found (%d)', $id));
        }

        return $download;
    }
}
