<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\MessageHandler;

use Doctrine\ORM\NonUniqueResultException;
use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Message\PreloadMessageInterface;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Spyck\VisualizationBundle\Service\DashboardService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[AsMessageHandler]
final readonly class PreloadMessageHandler
{
    public function __construct(private DashboardRepository $dashboardRepository, private DashboardService $dashboardService)
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(PreloadMessageInterface $preloadMessage): void
    {
        $dashboard = $this->getDashboardById($preloadMessage->getDashboardId());

        $this->dashboardService->getDashboardAsModel($dashboard, $preloadMessage->getVariables(), null, true);
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
}
