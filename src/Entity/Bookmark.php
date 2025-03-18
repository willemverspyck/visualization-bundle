<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;
use Spyck\VisualizationBundle\Controller\BookmarkController;
use Spyck\VisualizationBundle\Repository\BookmarkRepository;
use Spyck\VisualizationBundle\Utility\DateTimeUtility;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Validator;

#[Doctrine\Entity(repositoryClass: BookmarkRepository::class)]
#[Doctrine\Table(name: 'visualization_bookmark')]
class Bookmark implements TimestampInterface
{
    use TimestampTrait;

    #[Doctrine\Column(name: 'id', type: Types::INTEGER, options: ['unsigned' => true])]
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'IDENTITY')]
    #[Serializer\Groups(groups: [BookmarkController::GROUP_LIST])]
    private ?int $id = null;

    #[Doctrine\ManyToOne(targetEntity: UserInterface::class)]
    #[Doctrine\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    #[Validator\NotNull]
    private ?UserInterface $user = null;

    #[Doctrine\ManyToOne(targetEntity: Dashboard::class)]
    #[Doctrine\JoinColumn(name: 'dashboard_id', referencedColumnName: 'id', nullable: false)]
    #[Validator\NotNull]
    private Dashboard $dashboard;

    #[Doctrine\Column(name: 'name', type: Types::STRING, length: 128)]
    #[Validator\NotNull]
    #[Serializer\Groups(groups: [BookmarkController::GROUP_LIST])]
    private string $name;

    #[Doctrine\Column(name: 'variables', type: Types::JSON)]
    private array $variables;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDashboard(): Dashboard
    {
        return $this->dashboard;
    }

    public function setDashboard(Dashboard $dashboard): static
    {
        $this->dashboard = $dashboard;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    #[Serializer\Context([DateTimeNormalizer::FORMAT_KEY => DateTimeUtility::FORMAT_DATETIME])]
    #[Serializer\Groups(groups: [BookmarkController::GROUP_LIST])]
    public function getTimestamp(): DateTimeInterface
    {
        return $this->getTimestampCreated();
    }
}
