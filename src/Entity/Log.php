<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;
use Spyck\VisualizationBundle\Repository\LogRepository;
use Symfony\Component\Validator\Constraints as Validator;

#[Doctrine\Entity(repositoryClass: LogRepository::class)]
#[Doctrine\HasLifecycleCallbacks]
#[Doctrine\Table(name: 'visualization_log')]
class Log
{
    public const int TYPE_API = 1;
    public const string TYPE_API_NAME = 'API';
    public const int TYPE_MAIL = 2;
    public const string TYPE_MAIL_NAME = 'Mail';

    #[Doctrine\Column(name: 'id', type: Types::INTEGER, options: ['unsigned' => true])]
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[Doctrine\ManyToOne(targetEntity: Dashboard::class)]
    #[Doctrine\JoinColumn(name: 'dashboard_id', referencedColumnName: 'id', nullable: false)]
    #[Validator\NotNull]
    private Dashboard $dashboard;

    #[Doctrine\ManyToOne(targetEntity: UserInterface::class)]
    #[Doctrine\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private ?UserInterface $user = null;

    #[Doctrine\Column(name: 'timestamp', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $timestamp;

    #[Doctrine\Column(name: 'variables', type: Types::JSON)]
    private array $variables;

    #[Doctrine\Column(name: 'view', type: Types::STRING, length: 8, nullable: true)]
    private ?string $view = null;

    #[Doctrine\Column(name: 'type', type: Types::SMALLINT, options: ['unsigned' => true])]
    #[Validator\Choice(callback: [self::class, 'getTypes'])]
    #[Validator\NotNull]
    private int $type;

    #[Doctrine\Column(name: 'messages', type: Types::JSON, nullable: true)]
    private ?array $messages = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDashboard(): Dashboard
    {
        return $this->dashboard;
    }

    public function setDashboard(Dashboard $dashboard): static
    {
        $this->dashboard = $dashboard;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTimeImmutable $timestamp): static
    {
        $this->timestamp = $timestamp;

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

    public function getView(): ?string
    {
        return $this->view;
    }

    public function setView(?string $view): static
    {
        $this->view = $view;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getMessages(): ?array
    {
        return $this->messages;
    }

    public function setMessages(?array $messages): static
    {
        $this->messages = $messages;

        return $this;
    }

    public static function getTypes(bool $inverse = true): array
    {
        $data = [
            self::TYPE_API => self::TYPE_API_NAME,
            self::TYPE_MAIL => self::TYPE_MAIL_NAME,
        ];

        if (false === $inverse) {
            return $data;
        }

        return array_flip($data);
    }

    #[Doctrine\PrePersist]
    public function prePersist(): void
    {
        $date = new DateTimeImmutable();

        $this->setTimestamp($date);
    }
}
