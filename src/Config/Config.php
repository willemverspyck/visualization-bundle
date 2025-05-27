<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Spyck\VisualizationBundle\Context\ContextInterface;
use Spyck\VisualizationBundle\Context\ExcelContext;
use Spyck\VisualizationBundle\Controller\WidgetController;
use Spyck\VisualizationBundle\Field\FieldInterface;
use Symfony\Component\Serializer\Attribute as Serializer;

final class Config
{
    private FieldInterface $field;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private ?bool $abbreviation = null;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private ?int $precision = null;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private bool $merge = false;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private bool $separator = true;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private ?array $chart = null;

    /**
     * @var Collection<int, ContextInterface<ExcelContext>>
     */
    private Collection $contexts;

    public function __construct()
    {
        $this->contexts = new ArrayCollection();

        $this->addContext(new ExcelContext());
    }

    public function getField(): FieldInterface
    {
        return $this->field;
    }

    public function setField(FieldInterface $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function hasAbbreviation(): ?bool
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(?bool $abbreviation): static
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function setPrecision(?int $precision): static
    {
        $this->precision = $precision;

        return $this;
    }

    public function hasMerge(): bool
    {
        return $this->merge;
    }

    public function setMerge(bool $merge): static
    {
        $this->merge = $merge;

        return $this;
    }

    public function hasSeparator(): bool
    {
        return $this->separator;
    }

    public function setSeparator(bool $separator): static
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * @deprecated: Use contexts
     */
    public function getChart(): ?array
    {
        return $this->chart;
    }

    /**
     * @deprecated: Use contexts
     */
    public function setChart(?array $chart): static
    {
        $this->chart = $chart;

        return $this;
    }

    public function addContext(ContextInterface $context): void
    {
        $this->contexts->add($context);
    }

    /**
     * @return ContextInterface<ExcelContext>|null
     */
    public function getContext(string $view): ?ContextInterface
    {
        $contexts = $this->getContexts()->filter(function (ContextInterface $context) use ($view): bool {
            return $view === $context->getView();
        });

        return $contexts->isEmpty() ? null : $contexts->first();
    }

    /**
     * @return Collection<int, ContextInterface<ExcelContext>>
     */
    public function getContexts(): Collection
    {
        return $this->contexts;
    }
}
