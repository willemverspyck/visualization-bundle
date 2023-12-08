<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use Spyck\VisualizationBundle\Repository\WidgetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;
use Stringable;
use Symfony\Component\Validator\Constraints as Validator;

#[Doctrine\Entity(repositoryClass: WidgetRepository::class)]
#[Doctrine\Table(name: 'visualization_widget')]
class Widget extends AbstractTimestamp implements Stringable
{
    public const CHART_AREA = 'area';
    public const CHART_AREA_NAME = 'Area';
    public const CHART_BAR = 'bar';
    public const CHART_BAR_NAME = 'Bar';
    public const CHART_COLUMN = 'column';
    public const CHART_COLUMN_NAME = 'Column';
    public const CHART_COUNTRY = 'country';
    public const CHART_COUNTRY_NAME = 'Country';
    public const CHART_GANTT = 'gantt';
    public const CHART_GANTT_NAME = 'Gantt';
    public const CHART_LINE = 'line';
    public const CHART_LINE_NAME = 'Line';
    public const CHART_PIE = 'pie';
    public const CHART_PIE_NAME = 'Pie';
    public const CHART_REGION = 'region';
    public const CHART_REGION_NAME = 'Region';
    public const CHART_TABLE = 'table';
    public const CHART_TABLE_NAME = 'Table';

    #[Doctrine\Column(name: 'id', type: Types::SMALLINT, options: ['unsigned' => true])]
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[Doctrine\ManyToOne(targetEntity: GroupInterface::class)]
    #[Doctrine\JoinColumn(name: 'group_id', referencedColumnName: 'id', nullable: true)]
    private GroupInterface $group;

    #[Doctrine\Column(name: 'name', type: Types::STRING, length: 128)]
    #[Validator\NotNull(message: 'This value is required')]
    private string $name;

    #[Doctrine\Column(name: 'description', type: Types::TEXT, nullable: true)]
    protected ?string $description = null;

    #[Doctrine\Column(name: 'description_empty', type: Types::TEXT, nullable: true)]
    #[Validator\NotNull(message: 'This value is required')]
    protected ?string $descriptionEmpty = null;

    #[Doctrine\Column(name: 'adapter', type: Types::STRING, length: 128)]
    #[Validator\NotNull(message: 'This value is required')]
    private string $adapter;

    #[Doctrine\Column(name: 'charts', type: Types::JSON)]
    private array $charts;

    #[Doctrine\Column(name: 'active', type: Types::BOOLEAN)]
    private bool $active;

    /**
     * @var Collection<int, Block>
     */
    #[Doctrine\OneToMany(mappedBy: 'widget', targetEntity: Block::class)]
    private Collection $blocks;

    /**
     * @var Collection<int, GroupInterface>
     */
    #[Doctrine\ManyToMany(targetEntity: GroupInterface::class)]
    #[Doctrine\JoinTable(name: 'visualization_widget_group')]
    #[Doctrine\JoinColumn(name: 'widget_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Doctrine\InverseJoinColumn(name: 'group_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $groups;

    public function __construct()
    {
        $this->blocks = new ArrayCollection();
        $this->groups = new ArrayCollection();

        $this->setCharts([]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroup(): ?GroupInterface
    {
        return $this->group;
    }

    public function setGroup(?GroupInterface $group): static
    {
        $this->group = $group;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function setDescriptionEmpty(?string $descriptionEmpty): static
    {
        $this->descriptionEmpty = $descriptionEmpty;

        return $this;
    }

    public function getDescriptionEmpty(): ?string
    {
        return $this->descriptionEmpty;
    }

    public function getAdapter(): string
    {
        return $this->adapter;
    }

    public function setAdapter(string $adapter): static
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function getCharts(): array
    {
        return $this->charts;
    }

    public function setCharts(array $charts): static
    {
        $this->charts = $charts;

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

    public function addGroup(GroupInterface $group): static
    {
        $this->groups->add($group);

        return $this;
    }

    public function clearGroups(): void
    {
        $this->groups->clear();
    }

    /**
     * @return Collection<int, GroupInterface>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function removeGroup(GroupInterface $group): void
    {
        $this->groups->removeElement($group);
    }

    public static function getChartData(bool $inverse = false): array
    {
        $data = [
            self::CHART_AREA => self::CHART_AREA_NAME,
            self::CHART_COLUMN => self::CHART_COLUMN_NAME,
            self::CHART_COUNTRY => self::CHART_COUNTRY_NAME,
            self::CHART_GANTT => self::CHART_GANTT_NAME,
            self::CHART_LINE => self::CHART_LINE_NAME,
            self::CHART_PIE => self::CHART_PIE_NAME,
            self::CHART_REGION => self::CHART_REGION_NAME,
            self::CHART_TABLE => self::CHART_TABLE_NAME,
        ];

        if (false === $inverse) {
            return $data;
        }

        return array_flip($data);
    }

    public function __clone()
    {
        $this->id = null;

        $this->setName(sprintf('%s (Copy)', $this->getName()));
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
