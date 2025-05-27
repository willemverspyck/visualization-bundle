<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Controller;

use OpenApi\Attributes as OpenApi;
use Spyck\ApiExtension\Schema;
use Spyck\ApiExtension\Service\ResponseService;
use Spyck\VisualizationBundle\Entity\Mail;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Spyck\VisualizationBundle\Message\MailMessage;
use Spyck\VisualizationBundle\Payload\Mail as MailAsPayload;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Spyck\VisualizationBundle\Repository\MailRepository;
use Spyck\VisualizationBundle\Service\DashboardService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
#[OpenApi\Tag(name: 'Mails')]
final class MailController extends AbstractController
{
    public const string GROUP_LIST = 'spyck:visualization:mail:list';

    #[Route(path: '/api/mails', name: 'spyck_visualization_mail_list', methods: [Request::METHOD_GET])]
    #[Schema\BadRequest]
    #[Schema\Forbidden]
    #[Schema\NotFound]
    #[Schema\ResponseForList(type: Mail::class, groups: [self::GROUP_LIST])]
    public function list(MailRepository $mailRepository, ResponseService $responseService): Response
    {
        $mails = $mailRepository->getMailsBySubscribe(true);

        return $responseService->getResponseForList(data: $mails, groups: [self::GROUP_LIST]);
    }

    #[Route(path: '/api/mail/{mailId}', name: 'spyck_visualization_mail_post', requirements: ['mailId' => Requirement::DIGITS], methods: [Request::METHOD_POST])]
    public function post(MailRepository $mailRepository, ResponseService $responseService, TokenStorageInterface $tokenStorage, int $mailId): Response
    {
        $user = $tokenStorage->getToken()?->getUser();

        if (null === $user) {
            return $this->createAccessDeniedException();
        }

        $mail = $mailRepository->getMailById($mailId);

        if (null === $mail) {
            return $responseService->getResponseForItem();
        }

        $users = $mail->getUsers();

        if ($users->contains($user)) {
            return $responseService->getResponseForItem();
        }

        $users->add($user);

        $mailRepository->patchMail(mail: $mail, fields: ['users'], users: $users);

        return $responseService->getResponseForItem();
    }

    #[Route(path: '/api/mail/{mailId}', name: 'spyck_visualization_mail_subscribe', requirements: ['mailId' => Requirement::DIGITS], methods: [Request::METHOD_DELETE])]
    public function delete(MailRepository $mailRepository, ResponseService $responseService, TokenStorageInterface $tokenStorage, int $mailId): Response
    {
        $user = $tokenStorage->getToken()?->getUser();

        if (null === $user) {
            return $this->createAccessDeniedException();
        }

        $mail = $mailRepository->getMailById($mailId);

        if (null === $mail) {
            return $responseService->getResponseForItem();
        }

        $users = $mail->getUsers();
        $users->removeElement($user);

        $mailRepository->patchMail(mail: $mail, fields: ['users'], users: $users);

        return $responseService->getResponseForItem();
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/api/mail/dashboard/{dashboardId}', name: 'spyck_visualization_mail_dashboard', requirements: ['dashboardId' => Requirement::DIGITS], methods: [Request::METHOD_POST])]
    public function dashboard(DashboardRepository $dashboardRepository, DashboardService $dashboardService, MessageBusInterface $messageBus, ResponseService $responseService, TokenStorageInterface $tokenStorage, #[MapRequestPayload] MailAsPayload $mailAsPayload, int $dashboardId): Response
    {
        $dashboard = $dashboardRepository->getDashboardById($dashboardId);

        if (null === $dashboard) {
            return $responseService->getResponseForItem();
        }

        $errors = $dashboardService->getErrors($dashboard, $mailAsPayload->getVariables());

        if (count($errors) > 0) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        /** @var UserInterface $user */
        $user = $tokenStorage->getToken()?->getUser();

        if (null === $user) {
            throw $this->createAccessDeniedException();
        }

        $mailMessage = new MailMessage();
        $mailMessage->setDashboardId($dashboardId);
        $mailMessage->setUserId($user->getId());
        $mailMessage->setName($mailAsPayload->getName());
        $mailMessage->setDescription($mailAsPayload->getDescription());
        $mailMessage->setVariables($mailAsPayload->getVariables());
        $mailMessage->setView($mailAsPayload->getView());
        $mailMessage->setRoute($mailAsPayload->hasRoute());
        $mailMessage->setInline($mailAsPayload->isInline());
        $mailMessage->setMerge($mailAsPayload->isMerge());

        $messageBus->dispatch($mailMessage);

        $data = [
            'status' => 'OK',
        ];

        return new JsonResponse(data: $data);
    }
}
