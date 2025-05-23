<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;
use Spyck\VisualizationBundle\Repository\PreloadRepository;
use Stringable;
use Symfony\Component\Validator\Constraints as Validator;

#[Doctrine\Entity(repositoryClass: PreloadRepository::class)]
#[Doctrine\Table(name: 'visualization_preload')]
class Preload implements Stringable, TimestampInterface
{
    use TimestampTrait;

    #[Doctrine\Column(name: 'id', type: Types::INTEGER, options: ['unsigned' => true])]
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[Doctrine\ManyToOne(targetEntity: Dashboard::class)]
    #[Doctrine\JoinColumn(name: 'dashboard_id', referencedColumnName: 'id', nullable: false)]
    #[Validator\NotNull]
    private Dashboard $dashboard;

    #[Doctrine\Column(name: 'variables', type: Types::JSON)]
    private array $variables;

    #[Doctrine\Column(name: 'active', type: Types::BOOLEAN)]
    private bool $active;

    /**
     * @var Collection<int, ScheduleInterface>
     */
    #[Doctrine\ManyToMany(targetEntity: AbstractSchedule::class)]
    #[Doctrine\JoinTable(name: 'visualization_preload_schedule')]
    #[Doctrine\JoinColumn(name: 'preload_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Doctrine\InverseJoinColumn(name: 'schedule_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $schedules;

    #[Doctrine\ManyToMany(targetEntity: UserInterface::class)]
    #[Doctrine\JoinTable(name: 'visualization_preload_user')]
    #[Doctrine\JoinColumn(name: 'preload_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Doctrine\InverseJoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $users;

    public function __construct()
    {
        $this->schedules = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

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

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): static
    {
        $this->variables = $variables;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function addSchedule(ScheduleInterface $schedule): static
    {
        $this->schedules->add($schedule);

        return $this;
    }

    public function clearSchedules(): void
    {
        $this->schedules->clear();
    }

    /**
     * @return Collection<int, ScheduleInterface>
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function removeSchedule(ScheduleInterface $schedule): void
    {
        $this->schedules->removeElement($schedule);
    }

    public function addUser(UserInterface $user): static
    {
        $this->users->add($user);

        return $this;
    }

    public function clearUsers(): void
    {
        $this->users->clear();
    }

    /**
     * @return Collection<int, UserInterface>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function removeUser(UserInterface $user): void
    {
        $this->users->removeElement($user);
    }

    public function __toString(): string
    {
        return $this->getDashboard()->getName();
    }
}
