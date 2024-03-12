<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\MessageHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Entity\Log;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Spyck\VisualizationBundle\Message\MailMessage;
use Spyck\VisualizationBundle\Message\MailMessageInterface;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Model\Block as BlockAsModel;
use Spyck\VisualizationBundle\Model\Dashboard as DashboardAsModel;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Spyck\VisualizationBundle\Repository\LogRepository;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Spyck\VisualizationBundle\Repository\UserRepository;
use Spyck\VisualizationBundle\Service\DashboardService;
use Spyck\VisualizationBundle\Service\MailService;
use Spyck\VisualizationBundle\Service\ViewService;
use Spyck\VisualizationBundle\Utility\FileUtility;
use Spyck\VisualizationBundle\View\ViewInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[AsMessageHandler]
final class MailMessageHandler
{
    public function __construct(private readonly DashboardRepository $dashboardRepository, private readonly DashboardService $dashboardService, private readonly LogRepository $logRepository, private readonly MailService $mailService, private readonly TokenStorageInterface $tokenStorage, private readonly UserRepository $userRepository, private readonly ViewService $viewService)
    {
    }

    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function __invoke(MailMessageInterface $mailMessage): void
    {
        $user = $this->getUserById($mailMessage->getUser());

        $token = $this->tokenStorage->getToken();

        $usernamePasswordToken = new UsernamePasswordToken($user, get_class($this), $user->getRoles());

        $this->tokenStorage->setToken($usernamePasswordToken);

        $dashboard = $this->getDashboardById($mailMessage->getId());

        $this->sendMailWithMessage($mailMessage, $user, $dashboard);

        $this->tokenStorage->setToken($token);

        $this->logRepository->putLog(user: $user, dashboard: $dashboard, variables: $mailMessage->getVariables(), view: $mailMessage->getView(), type: Log::TYPE_MAIL);
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
     * Check if the dashboard exists and if user has access.
     *
     * @throws AuthenticationException
     * @throws NonUniqueResultException
     */
    private function getDashboardById(int $id): Dashboard
    {
        $dashboard = $this->dashboardRepository->getDashboardById($id);

        if (null === $dashboard) {
            throw new AuthenticationException(sprintf('Dashboard not found (%d) (Access Denied)', $id));
        }

        return $dashboard;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws TransportExceptionInterface
     */
    private function sendMailWithMessage(MailMessageInterface $mailMessage, UserInterface $user, Dashboard $dashboard): void
    {
        $dashboardAsModel = $this->dashboardService->getDashboardAsModel($dashboard, $mailMessage->getVariables(), $mailMessage->getView(), true);

        $subject = [
            $mailMessage->getName(),
        ];

        foreach ($dashboardAsModel->getParametersAsString() as $parameter) {
            $subject[] = $parameter;
        }

        $data = [
            'dashboard' => $dashboardAsModel,
            'mail' => $mailMessage,
        ];

        $attachments = $this->getAttachments($dashboardAsModel, $mailMessage);

        $this->mailService->sendMail($user->getEmail(), $user->getName(), implode(' | ', $subject), '@SpyckVisualization/mail/index.html.twig', $data, $attachments->toArray());
    }

    private function getAttachments(DashboardAsModel $dashboardAsModel, MailMessage $mailMessage): ArrayCollection
    {
        $attachments = new ArrayCollection();

        if (null === $mailMessage->getView()) {
            return $attachments;
        }

        $view = $this->viewService->getView($mailMessage->getView());

        $merge = null === $view->isMerge() ? $mailMessage->isMerge() : $view->isMerge();

        if ($merge) {
            $attachments->add($this->getAttachment($dashboardAsModel, $view));

            return $attachments;
        }

        return $dashboardAsModel->getBlocks()->map(function (BlockAsModel $block) use ($dashboardAsModel, $view): DataPart {
            $dashboardAsModelClone = clone $dashboardAsModel;
            $dashboardAsModelClone->addBlock($block);

            return $this->getAttachment($dashboardAsModelClone, $view, $block->getName());
        });
    }

    private function getAttachment(DashboardAsModel $dashboard, ViewInterface $view, string $name = null): DataPart
    {
        $content = $view->getContent($dashboard);

        $filename = $this->getFilename(null === $name ? $dashboard->getName() : $name, $dashboard->getParametersAsStringForSlug(), $view->getExtension());

        return new DataPart($content, $filename, $view->getContentType());
    }

    private function getFilename(string $name, array $parameters, string $extension): string
    {
        $filename = [
            $name,
        ];

        foreach ($parameters as $parameter) {
            $filename[] = sprintf('%s', $parameter);
        }

        $slug = FileUtility::filter(implode('-', $filename));

        return sprintf('%s.%s', $slug, $extension);
    }
}
