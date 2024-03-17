<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;
use Spyck\VisualizationBundle\Repository\FavoriteRepository;
use Symfony\Component\Validator\Constraints as Validator;

#[Doctrine\Entity(repositoryClass: FavoriteRepository::class)]
#[Doctrine\Table(name: 'visualization_favorite')]
class Favorite implements TimestampInterface
{
    use TimestampTrait;

    #[Doctrine\Column(name: 'id', type: Types::INTEGER, options: ['unsigned' => true])]
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[Doctrine\ManyToOne(targetEntity: UserInterface::class)]
    #[Doctrine\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[Validator\NotNull]
    private UserInterface $user;

    #[Doctrine\ManyToOne(targetEntity: Dashboard::class)]
    #[Doctrine\JoinColumn(name: 'dashboard_id', referencedColumnName: 'id', nullable: false)]
    #[Validator\NotNull]
    private Dashboard $dashboard;

    public function __construct()
    {
    }

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

    public function getDashboard(): Dashboard
    {
        return $this->dashboard;
    }

    public function setDashboard(Dashboard $dashboard): static
    {
        $this->dashboard = $dashboard;

        return $this;
    }
}
