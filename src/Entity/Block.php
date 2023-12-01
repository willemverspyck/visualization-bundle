<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;
use Stringable;

#[Doctrine\Table(name: 'visualization_block')]
#[Doctrine\Entity]
class Block extends AbstractTimestamp implements Stringable
{
    public const SIZE_LARGE = 'L';
    public const SIZE_LARGE_NAME = 'Large';
    public const SIZE_MEDIUM = 'M';
    public const SIZE_MEDIUM_NAME = 'Medium';
    public const SIZE_SMALL = 'S';
    public const SIZE_SMALL_NAME = 'Small';

    #[Doctrine\Column(name: 'id', type: Types::INTEGER, options: ['unsigned' => true])]
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[Doctrine\ManyToOne(targetEntity: Dashboard::class, inversedBy: 'blocks')]
    #[Doctrine\JoinColumn(name: 'dashboard_id', referencedColumnName: 'id', nullable: false)]
    private Dashboard $dashboard;

    #[Doctrine\ManyToOne(targetEntity: Widget::class, inversedBy: 'blocks')]
    #[Doctrine\JoinColumn(name: 'widget_id', referencedColumnName: 'id', nullable: false)]
    private Widget $widget;

    #[Doctrine\Column(name: 'name', type: Types::STRING, length: 128, nullable: true)]
    private ?string $name = null;

    #[Doctrine\Column(name: 'description', type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Doctrine\Column(name: 'size', type: Types::STRING, length: 128)]
    private string $size;

    #[Doctrine\Column(name: 'position', type: Types::SMALLINT, options: ['unsigned' => true])]
    private int $position;

    #[Doctrine\Column(name: 'variables', type: Types::JSON)]
    private array $variables;

    #[Doctrine\Column(name: 'chart', type: Types::STRING, length: 32, nullable: true)]
    private ?string $chart = null;

    #[Doctrine\Column(name: 'filter', type: Types::BOOLEAN)]
    private bool $filter;

    #[Doctrine\Column(name: 'filter_view', type: Types::BOOLEAN)]
    private bool $filterView;

    #[Doctrine\Column(name: 'active', type: Types::BOOLEAN)]
    private bool $active;
    
    public function __construct()
    {
        $this->setFilter(true);
        $this->setFilterView(true);
        $this->setActive(true);
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

    public function getWidget(): Widget
    {
        return $this->widget;
    }

    public function setWidget(Widget $widget): static
    {
        $this->widget = $widget;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
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

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(string $size): static
    {
        $this->size = $size;

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

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): static
    {
        $this->variables = $variables;

        return $this;
    }

    public function getChart(): ?string
    {
        return $this->chart;
    }

    public function setChart(?string $chart): static
    {
        $this->chart = $chart;

        return $this;
    }

    public function hasFilter(): bool
    {
        return $this->filter;
    }

    public function setFilter(bool $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    public function hasFilterView(): bool
    {
        return $this->filterView;
    }

    public function setFilterView(bool $filterView): static
    {
        $this->filterView = $filterView;

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

    public static function getSizeData(bool $inverse = false): array
    {
        $data = [
            self::SIZE_LARGE => self::SIZE_LARGE_NAME,
            self::SIZE_MEDIUM => self::SIZE_MEDIUM_NAME,
            self::SIZE_SMALL => self::SIZE_SMALL_NAME,
        ];

        if (false === $inverse) {
            return $data;
        }

        return array_flip($data);
    }

    public function __toString(): string
    {
        return sprintf('%s at position %d', $this->getDashboard(), $this->getPosition());
    }
}
