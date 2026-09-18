<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;
use SortDirection;
use Spyck\VisualizationBundle\Controller\CategoryController;
use Spyck\VisualizationBundle\Repository\CategoryRepository;
use Stringable;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Validator;

#[Doctrine\Entity(repositoryClass: CategoryRepository::class)]
#[Doctrine\Table(name: 'visualization_category')]
class Category implements Stringable, TimestampInterface
{
    use TimestampTrait;

    #[Doctrine\Column(name: 'id', type: Types::SMALLINT, options: ['unsigned' => true])]
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'IDENTITY')]
    #[Serializer\Groups(groups: [CategoryController::GROUP_ITEM, CategoryController::GROUP_LIST])]
    private ?int $id = null;

    #[Doctrine\Column(name: 'name', type: Types::STRING, length: 128)]
    #[Serializer\Groups(groups: [CategoryController::GROUP_ITEM, CategoryController::GROUP_LIST])]
    #[Validator\NotNull]
    private string $name;

    #[Doctrine\Column(name: 'code', type: Types::STRING, length: 128, nullable: true)]
    #[Serializer\Groups(groups: [CategoryController::GROUP_ITEM, CategoryController::GROUP_LIST])]
    private ?string $code = null;

    #[Doctrine\Column(name: 'description', type: Types::TEXT, nullable: true)]
    #[Serializer\Groups(groups: [CategoryController::GROUP_ITEM, CategoryController::GROUP_LIST])]
    private ?string $description;

    #[Doctrine\Column(name: 'position', type: Types::SMALLINT, options: ['unsigned' => true])]
    #[Validator\NotNull]
    private int $position;

    #[Doctrine\Column(name: 'active', type: Types::BOOLEAN)]
    private bool $active;

    /**
     * @var Collection<int, Dashboard>
     */
    #[Doctrine\ManyToMany(targetEntity: Dashboard::class, mappedBy: 'categories')]
    #[Doctrine\OrderBy(['score' => SortDirection::Descending])]
    #[Serializer\Groups(groups: [CategoryController::GROUP_ITEM, CategoryController::GROUP_LIST])]
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

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
