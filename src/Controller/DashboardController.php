<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Exception;
use OpenApi\Attributes as OpenApi;
use Psr\Cache\InvalidArgumentException;
use Spyck\ApiExtension\Schema;
use Spyck\ApiExtension\Service\ResponseService;
use Spyck\VisualizationBundle\Entity\Log;
use Spyck\VisualizationBundle\Model\Dashboard as DashboardAsModel;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Spyck\VisualizationBundle\Repository\LogRepository;
use Spyck\VisualizationBundle\Service\DashboardService;
use Spyck\VisualizationBundle\View\ViewInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Throwable;

#[AsController]
#[OpenApi\Tag(name: 'Dashboard')]
final class DashboardController extends AbstractController
{
    public const string GROUP_ITEM = 'spyck:visualization:dashboard:item';

    /**
     * @throws Throwable
     * @throws NonUniqueResultException
     */
    #[Route(path: '/dashboard/{dashboardId}', name: 'spyck_visualization_dashboard_show', requirements: ['dashboardId' => Requirement::DIGITS], methods: [Request::METHOD_GET])]
    public function show(DashboardRepository $dashboardRepository, Request $request, int $dashboardId): Response
    {
        $dashboard = $dashboardRepository->getDashboardById($dashboardId);

        if (null === $dashboard) {
            throw $this->createNotFoundException('The dashboard does not exist');
        }

        $parameters = $request->query->all();
        $parameters['dashboardId'] = $dashboard->getId();

        return $this->render('@SpyckVisualization/dashboard/index.html.twig', [
            'parameters' => $parameters,
        ]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Route(path: '/api/dashboard/{dashboardId}', name: 'spyck_visualization_dashboard_item', requirements: ['dashboardId' => Requirement::DIGITS], methods: [Request::METHOD_GET])]
    #[Schema\BadRequest]
    #[Schema\Forbidden]
    #[Schema\NotFound]
    #[Schema\ResponseForItem(type: DashboardAsModel::class, groups: [self::GROUP_ITEM])]
    public function item(DashboardRepository $dashboardRepository, DashboardService $dashboardService, LogRepository $logRepository, Request $request, ResponseService $responseService, TokenStorageInterface $tokenStorage, int $dashboardId): Response
    {
        $dashboard = $dashboardRepository->getDashboardById($dashboardId);

        if (null === $dashboard) {
            return $responseService->getResponseForItem();
        }

        $variables = $request->query->all();

        $errors = $dashboardService->getErrors($dashboard, $variables);

        if (count($errors) > 0) {
            $dashboardAsModel = [
                'id' => $dashboard->getId(),
                'name' => $dashboard->getName(),
                'description' => $dashboard->getDescription(),
                'variables' => $variables,
                'errors' => $errors,
            ];

            return new JsonResponse($dashboardAsModel);
        }

        $dashboardAsModel = $dashboardService->getDashboardAsModel($dashboard, $variables);

        $user = $tokenStorage->getToken()?->getUser();

        $logRepository->putLog(user: $user, dashboard: $dashboard, variables: $dashboardAsModel->getVariables(), view: ViewInterface::JSON, type: Log::TYPE_API);

        return $responseService->getResponseForItem(data: $dashboardAsModel, groups: [self::GROUP_ITEM]);
    }
}
