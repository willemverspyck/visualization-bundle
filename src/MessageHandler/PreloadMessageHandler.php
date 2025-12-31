<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\MessageHandler;

use Doctrine\ORM\NonUniqueResultException;
use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Spyck\VisualizationBundle\Message\PreloadMessageInterface;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Spyck\VisualizationBundle\Repository\UserRepository;
use Spyck\VisualizationBundle\Service\DashboardService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[AsMessageHandler(sign: true)]
final readonly class PreloadMessageHandler
{
    public function __construct(private DashboardRepository $dashboardRepository, private DashboardService $dashboardService, private TokenStorageInterface $tokenStorage, private UserRepository $userRepository)
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(PreloadMessageInterface $preloadMessage): void
    {
        if (null === $preloadMessage->getUserId()) {
            $this->executePreload($preloadMessage);

            return;
        }

        $user = $this->getUserById($preloadMessage->getUserId());

        $token = $this->tokenStorage->getToken();

        $usernamePasswordToken = new UsernamePasswordToken($user, get_class($this), $user->getRoles());

        $this->tokenStorage->setToken($usernamePasswordToken);

        $this->executePreload($preloadMessage);

        $this->tokenStorage->setToken($token);
    }

    private function executePreload(PreloadMessageInterface $preloadMessage): void
    {
        $dashboard = $this->getDashboardById($preloadMessage->getDashboardId());

        $this->dashboardService->getDashboardAsModel($dashboard, $preloadMessage->getVariables(), null, true);
    }

    /**
     * Check if the dashboard exists.
     *
     * @throws AuthenticationException
     * @throws NonUniqueResultException
     */
    private function getDashboardById(int $id): Dashboard
    {
        $dashboard = $this->dashboardRepository->getDashboardById($id, false);

        if (null === $dashboard) {
            throw new AuthenticationException(sprintf('Dashboard not found (%d)', $id));
        }

        return $dashboard;
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
}
