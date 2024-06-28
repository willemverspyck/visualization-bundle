<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;
use Spyck\VisualizationBundle\Controller\CategoryController;
use Spyck\VisualizationBundle\Repository\CategoryRepository;
use Stringable;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Validator;

#[Doctrine\Entity(repositoryClass: CategoryRepository::class)]
#[Doctrine\Table(name: 'visualization_category')]
class Category implements Stringable, TimestampInterface
{
    use TimestampTrait;

    #[Doctrine\Column(name: 'id', type: Types::SMALLINT, options: ['unsigned' => true])]
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'IDENTITY')]
    #[Serializer\Groups(groups: [MenuController::GROUP_LIST])]
    private ?int $id = null;

    #[Doctrine\Column(name: 'name', type: Types::STRING, length: 128)]
    #[Serializer\Groups(groups: [CategoryController::GROUP_LIST])]
    #[Validator\NotNull]
    private string $name;

    #[Doctrine\Column(name: 'active', type: Types::BOOLEAN)]
    private bool $active;

    /**
     * @var Collection<int, Dashboard>
     */
    #[Doctrine\OneToMany(mappedBy: 'category', targetEntity: Dashboard::class)]
    #[Serializer\Groups(groups: [CategoryController::GROUP_LIST])]
    private Collection $dashboards;

    public function __construct()
    {
        $this->dashboards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function addDashboard(Dashboard $dashboard): static
    {
        $dashboard->setCategory($this);

        $this->dashboards->add($dashboard);

        return $this;
    }

    public function clearDashboards(): void
    {
        $this->dashboards->clear();
    }

    /**
     * @return Collection<int, Dashboard>
     */
    public function getDashboards(): Collection
    {
        return $this->dashboards;
    }

    public function removeDashboard(Dashboard $dashboard): void
    {
        $this->dashboards->removeElement($dashboard);
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
