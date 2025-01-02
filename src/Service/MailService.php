<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Spyck\VisualizationBundle\Entity\Mail;
use Spyck\VisualizationBundle\Entity\ScheduleInterface;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Spyck\VisualizationBundle\Message\MailMessage;
use Spyck\VisualizationBundle\Repository\MailRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\Mime\Part\DataPart;

readonly class MailService
{
    public function __construct(private BodyRendererInterface $bodyRenderer, private MailRepository $mailRepository, private MessageBusInterface $messageBus, private MailerInterface $mailer, #[Autowire(param: 'spyck.visualization.config.mail.fromEmail')] private string $fromEmail, #[Autowire(param: 'spyck.visualization.config.mail.fromName')] private string $fromName)
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function executeMail(string $toEmail, ?string $toName, string $subject, string $template, array $data = [], array $attachments = []): void
    {
        $email = new TemplatedEmail();

        $from = new Address($this->fromEmail, $this->fromName);
        $to = new Address($toEmail, null === $toName ? '' : $toName);

        $email
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($data);

        foreach ($attachments as $attachment) {
            if ($attachment instanceof DataPart) {
                $email->addPart($attachment);
            }
        }

        $this->bodyRenderer->render($email);

        $this->mailer->send($email);
    }

    public function executeMailAsMessage(Mail $mail, UserInterface $user, array $parameters = []): void
    {
        $dashboard = $mail->getDashboard();

        $mailMessage = new MailMessage();
        $mailMessage->setId($dashboard->getId());
        $mailMessage->setUser($user->getId());
        $mailMessage->setName($mail->getName());
        $mailMessage->setDescription($mail->getDescription());
        $mailMessage->setVariables(array_merge($mail->getVariables(), $parameters));
        $mailMessage->setView($mail->getView());
        $mailMessage->setInline($mail->isInline());
        $mailMessage->setRoute($mail->hasRoute());
        $mailMessage->setMerge($mail->isMerge());

        $this->messageBus->dispatch($mailMessage);
    }

    public function executeMailAsMessageBySchedule(ScheduleInterface $schedule, array $parameters = []): void
    {
        $mails = $this->mailRepository->getMailsBySchedule($schedule);

        foreach ($mails as $mail) {
            foreach ($mail->getUsers() as $user) {
                $this->executeMailAsMessage($mail, $user, $parameters);
            }
        }
    }
}
