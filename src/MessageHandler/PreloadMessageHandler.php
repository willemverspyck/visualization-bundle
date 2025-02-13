<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\MessageHandler;

use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Message\PreloadMessageInterface;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Spyck\VisualizationBundle\Repository\UserRepository;
use Spyck\VisualizationBundle\Service\DashboardService;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[AsMessageHandler]
final class PreloadMessageHandler
{
    public function __construct(private readonly DashboardRepository $dashboardRepository, private readonly DashboardService $dashboardService, private readonly TokenStorageInterface $tokenStorage, private readonly UserRepository $userRepository)
    {
    }

    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function __invoke(PreloadMessageInterface $preloadMessage): void
    {
        $this->executePreload($preloadMessage);
    }

    /**
     * Check if the dashboard exists and if user has access.
     *
     * @throws AuthenticationException
     * @throws NonUniqueResultException
     */
    private function getDashboardById(int $id): Dashboard
    {
        $dashboard = $this->dashboardRepository->getDashboardById($id);

        if (null === $dashboard) {
            throw new AuthenticationException(sprintf('Dashboard not found (%d)', $id));
        }

        return $dashboard;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws TransportExceptionInterface
     */
    private function executePreload(PreloadMessageInterface $preloadMessage): void
    {
        $dashboard = $this->getDashboardById($preloadMessage->getId());

        $this->dashboardService->getDashboardAsModel($dashboard, $preloadMessage->getVariables(), null, true);
    }
}
