<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Exception;
use OpenApi\Attributes as OpenApi;
use Psr\Cache\InvalidArgumentException;
use Spyck\ApiExtension\Schema;
use Spyck\ApiExtension\Service\ResponseService;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Entity\Log;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Spyck\VisualizationBundle\Form\DashboardMailType;
use Spyck\VisualizationBundle\Message\MailMessage;
use Spyck\VisualizationBundle\Model\Dashboard as DashboardAsModel;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Spyck\VisualizationBundle\Repository\LogRepository;
use Spyck\VisualizationBundle\Service\DashboardService;
use Spyck\VisualizationBundle\View\ViewInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Throwable;

#[AsController]
#[OpenApi\Tag(name: 'Dashboard')]
final class DashboardController extends AbstractController
{
    /**
     * @throws Throwable
     * @throws NonUniqueResultException
     */
    #[Route(path: '/dashboard/{dashboardId}', name: 'spyck_visualization_dashboard_show', requirements: ['dashboardId' => Requirement::DIGITS], methods: [Request::METHOD_GET])]
    public function show(DashboardRepository $dashboardRepository, Request $request, int $dashboardId): Response
    {
        $dashboard = $dashboardRepository->getDashboardById($dashboardId);

        if (false === $dashboard instanceof Dashboard) {
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
    #[Schema\ResponseForItem(type: DashboardAsModel::class, groups: ['spyck:visualization:dashboard:item'])]
    public function item(DashboardRepository $dashboardRepository, DashboardService $dashboardService, LogRepository $logRepository, Request $request, ResponseService $responseService, TokenStorageInterface $tokenStorage, int $dashboardId): Response
    {
        $dashboard = $dashboardRepository->getDashboardById($dashboardId);

        if (null === $dashboard) {
            return $responseService->getResponseForItem();
        }

        $variables = $request->query->all();

        $requests = $dashboardService->checkDashboardParameterData($dashboard, $variables);

        if (null === $requests) {
            $data = $dashboardService->getDashboardAsModel($dashboard, $variables);

            $user = $tokenStorage->getToken()?->getUser();

            $logRepository->putLog(user: $user, dashboard: $dashboard, variables: $data->getVariables(), view: ViewInterface::JSON, type: Log::TYPE_API);

            return $responseService->getResponseForItem(data: $data, groups: ['spyck:visualization:dashboard:item']);
        }

        $data = [
            'error' => true,
            'name' => $dashboard->getName(),
            'requests' => $requests,
        ];

        return new JsonResponse($data);
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/api/dashboard/{dashboardId}/mail', name: 'spyck_visualization_dashboard_mail', requirements: ['dashboardId' => Requirement::DIGITS], methods: [Request::METHOD_POST])]
    public function mail(DashboardRepository $dashboardRepository, DashboardService $dashboardService, MessageBusInterface $messageBus, Request $request, ResponseService $responseService, TokenStorageInterface $tokenStorage, int $dashboardId): Response
    {
        $dashboard = $dashboardRepository->getDashboardById($dashboardId);

        if (null === $dashboard) {
            return $responseService->getResponseForItem();
        }

        $data = json_decode($request->getContent(), true);

        if (false === array_key_exists('variables', $data)) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $requests = $dashboardService->checkDashboardParameterData($dashboard, $data['variables']);

        if (null !== $requests) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        return $this->getForm(DashboardMailType::class, new MailMessage(), $data, function (MailMessage $mailMessage) use ($dashboard, $tokenStorage, $messageBus, $data): array {
            /** @var UserInterface $user */
            $user = $tokenStorage->getToken()->getUser();

            $mailMessage->setId($dashboard->getId());
            $mailMessage->setUser($user->getId());
            $mailMessage->setVariables($data['variables']);

            $messageBus->dispatch($mailMessage);

            return [];
        });
    }
}
