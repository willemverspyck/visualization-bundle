<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\MessageHandler;

use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Entity\Download;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Spyck\VisualizationBundle\Event\DownloadEvent;
use Spyck\VisualizationBundle\Message\DownloadMessageInterface;
use Spyck\VisualizationBundle\Repository\DownloadRepository;
use Spyck\VisualizationBundle\Repository\UserRepository;
use Spyck\VisualizationBundle\Service\DownloadService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Throwable;

#[AsMessageHandler]
final class DownloadMessageHandler
{
    public function __construct(private readonly DownloadRepository $downloadRepository, private readonly DownloadService $downloadService, private readonly EventDispatcherInterface $eventDispatcher, private readonly TokenStorageInterface $tokenStorage, private readonly UserRepository $userRepository)
    {
    }

    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function __invoke(DownloadMessageInterface $downloadMessage): void
    {
        $download = $this->getDownloadById($downloadMessage->getId());
        $user = $this->getUserById($downloadMessage->getUserId());

        $token = $this->tokenStorage->getToken();

        $usernamePasswordToken = new UsernamePasswordToken($user, get_class($this), $user->getRoles());

        $this->tokenStorage->setToken($usernamePasswordToken);

        $this->downloadService->executeDownload($download);

        $this->tokenStorage->setToken($token);

        $downloadEvent = new DownloadEvent($download);

        $this->eventDispatcher->dispatch($downloadEvent);
    }

    /**
     * @throws NonUniqueResultException
     * @throws UnrecoverableMessageHandlingException
     */
    private function getUserById(int $id): UserInterface
    {
        $user = $this->userRepository->getUserById($id);

        if (null === $user) {
            throw new UnrecoverableMessageHandlingException(sprintf('User not found (%d)', $id));
        }

        return $user;
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
