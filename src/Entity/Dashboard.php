<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Validator;

#[Doctrine\Entity(repositoryClass: DashboardRepository::class)]
#[Doctrine\Table(name: 'visualization_dashboard')]
#[UniqueEntity(fields: 'code')]
class Dashboard implements Stringable, TimestampInterface
{
    use TimestampTrait;

    #[Doctrine\Column(name: 'id', type: Types::SMALLINT, options: ['unsigned' => true])]
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[Doctrine\ManyToOne(targetEntity: UserInterface::class)]
    #[Doctrine\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private ?UserInterface $user;

    #[Doctrine\Column(name: 'name', type: Types::STRING, length: 128)]
    #[Validator\NotBlank]
    #[Validator\NotNull]
    private string $name;

    #[Doctrine\Column(name: 'code', type: Types::STRING, length: 128, unique: true, nullable: true)]
    private ?string $code;

    #[Doctrine\Column(name: 'description', type: Types::TEXT, nullable: true)]
    private ?string $description;

    #[Doctrine\Column(name: 'variables', type: Types::JSON)]
    private array $variables;

    #[Doctrine\Column(name: 'active', type: Types::BOOLEAN)]
    private bool $active;

    /**
     * @var Collection<int, Block>
     */
    #[Doctrine\OneToMany(mappedBy: 'dashboard', targetEntity: Block::class, cascade: ['persist'], orphanRemoval: true)]
    #[Doctrine\OrderBy(value: ['position' => 'ASC'])]
    #[Validator\Valid]
    private Collection $blocks;

    /**
     * @var Collection<int, Category>
     */
    #[Doctrine\ManyToMany(targetEntity: Category::class, inversedBy: 'dashboards')]
    #[Doctrine\JoinTable(name: 'visualization_dashboard_category')]
    #[Doctrine\JoinColumn(name: 'dashboard_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Doctrine\InverseJoinColumn(name: 'category_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $categories;

    public function __construct()
    {
        $this->blocks = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

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

    public function addBlock(Block $block): static
    {
        $block->setDashboard($this);

        $this->blocks->add($block);

        return $this;
    }

    public function clearBlocks(): void
    {
        $this->blocks->clear();
    }

    /**
     * @return Collection<int, Block>
     */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function removeBlock(Block $block): void
    {
        $this->blocks->removeElement($block);
    }

    public function addCategory(Category $category): static
    {
        $this->categories->add($category);

        return $this;
    }

    public function clearCategories(): void
    {
        $this->categories->clear();
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function removeCategory(Category $category): void
    {
        $this->categories->removeElement($category);
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
