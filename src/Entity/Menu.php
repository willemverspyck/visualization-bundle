<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;
use Spyck\VisualizationBundle\Controller\MenuController;
use Spyck\VisualizationBundle\Repository\MenuRepository;
use Stringable;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Validator;

#[Doctrine\Entity(repositoryClass: MenuRepository::class)]
#[Doctrine\Table(name: 'visualization_menu')]
class Menu implements Stringable
{
    #[Doctrine\Column(name: 'id', type: Types::SMALLINT, options: ['unsigned' => true])]
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'IDENTITY')]
    #[Serializer\Groups(groups: [MenuController::GROUP_LIST])]
    private ?int $id = null;

    #[Doctrine\ManyToOne(targetEntity: Menu::class, inversedBy: 'children')]
    #[Doctrine\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true)]
    private ?Menu $parent = null;

    #[Doctrine\ManyToOne(targetEntity: Dashboard::class)]
    #[Doctrine\JoinColumn(name: 'dashboard_id', referencedColumnName: 'id', nullable: true)]
    private ?Dashboard $dashboard = null;

    #[Doctrine\Column(name: 'name', type: Types::STRING, length: 128)]
    #[Serializer\Groups(groups: [MenuController::GROUP_LIST])]
    #[Validator\NotNull]
    private string $name;

    /**
     * @var array<string, string|int>
     */
    #[Doctrine\Column(name: 'variables', type: Types::JSON)]
    #[Serializer\Groups(groups: [MenuController::GROUP_LIST])]
    private array $variables;

    #[Doctrine\Column(name: 'position', type: Types::SMALLINT, options: ['unsigned' => true])]
    #[Validator\NotNull]
    private int $position;

    #[Doctrine\Column(name: 'active', type: Types::BOOLEAN)]
    private bool $active;

    /**
     * @var Collection<int, Menu>
     */
    #[Doctrine\OneToMany(mappedBy: 'parent', targetEntity: Menu::class)]
    #[Serializer\Groups(groups: [MenuController::GROUP_LIST])]
    private Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();

        $this->setVariables([]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?Menu
    {
        return $this->parent;
    }

    public function setParent(?Menu $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getDashboard(): ?Dashboard
    {
        return $this->dashboard;
    }

    public function setDashboard(?Dashboard $dashboard): static
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

    public function addChild(Menu $child): static
    {
        $this->children->add($child);

        return $this;
    }

    public function clearChildren(): void
    {
        $this->children->clear();
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function removeChild(Menu $child): void
    {
        $this->children->removeElement($child);
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
