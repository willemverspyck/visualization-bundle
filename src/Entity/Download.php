<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;
use Spyck\VisualizationBundle\Controller\DownloadController;
use Stringable;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Validator;

#[Doctrine\Entity(repositoryClass: DownloadRepository::class)]
#[Doctrine\Table(name: 'visualization_download')]
class Download implements Stringable, TimestampInterface
{
    use TimestampTrait;

    public const string STATUS_COMPLETE = 'complete';
    public const string STATUS_COMPLETE_NAME = 'Complete';
    public const string STATUS_ERROR = 'error';
    public const string STATUS_ERROR_NAME = 'Error';
    public const string STATUS_PENDING = 'pending';
    public const string STATUS_PENDING_NAME = 'Pending';

    #[Doctrine\Column(name: 'id', type: Types::INTEGER, options: ['unsigned' => true])]
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'IDENTITY')]
    #[Serializer\Groups(groups: [DownloadController::GROUP_LIST, DownloadController::GROUP_WIDGET])]
    private ?int $id = null;

    #[Doctrine\ManyToOne(targetEntity: UserInterface::class)]
    #[Doctrine\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private UserInterface $user;

    #[Doctrine\ManyToOne(targetEntity: Widget::class)]
    #[Doctrine\JoinColumn(name: 'widget_id', referencedColumnName: 'id', nullable: false)]
    #[Validator\NotNull]
    private Widget $widget;

    #[Doctrine\Column(name: 'variables', type: Types::JSON)]
    private array $variables;

    #[Doctrine\Column(name: 'view', type: Types::STRING, length: 8, nullable: false)]
    #[Serializer\Groups(groups: [DownloadController::GROUP_LIST])]
    private string $view;

    #[Doctrine\Column(name: 'status', type: Types::STRING, length: 16, nullable: true)]
    #[Serializer\Groups(groups: [DownloadController::GROUP_LIST])]
    private ?string $status = null;

    #[Doctrine\Column(name: 'duration', type: Types::INTEGER, nullable: true)]
    private ?int $duration = null;

    #[Doctrine\Column(name: 'messages', type: Types::JSON, nullable: true)]
    private ?array $messages = null;

    #[Doctrine\Column(name: 'timestamp', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Serializer\Groups(groups: [DownloadController::GROUP_LIST])]
    private ?DateTimeImmutable $timestamp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getWidget(): Widget
    {
        return $this->widget;
    }

    public function setWidget(Widget $widget): static
    {
        $this->widget = $widget;

        return $this;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): static
    {
        $this->variables = $variables;

        return $this;
    }

    public function getView(): string
    {
        return $this->view;
    }

    public function setView(string $view): static
    {
        $this->view = $view;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getMessages(): ?array
    {
        return $this->messages;
    }

    public function setMessages(?array $messages): self
    {
        $this->messages = $messages;

        return $this;
    }

    public function getTimestamp(): ?DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setTimestamp(?DateTimeImmutable $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s', $this->getWidget());
    }
}
