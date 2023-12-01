<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Exception;
use Spyck\VisualizationBundle\Entity\Log;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Repository\LogRepository;
use Spyck\VisualizationBundle\View\ViewInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class LogService
{
    public function __construct(private LogRepository $logRepository, private TokenStorageInterface $tokenStorage, private RequestStack $requestStack)
    {
    }

    /**
     * @throws Exception
     */
    public function putLog(Dashboard $dashboard): void
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        $variables = array_filter($this->requestStack->getCurrentRequest()->query->all());

        $this->logRepository->putLog(user: $user, dashboard: $dashboard, variables: $variables, view: ViewInterface::JSON, type: Log::TYPE_API);
    }
}
