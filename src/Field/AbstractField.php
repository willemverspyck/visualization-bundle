<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Field;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Spyck\VisualizationBundle\Controller\WidgetController;
use Spyck\VisualizationBundle\Format\FormatInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

abstract class AbstractField
{
    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private string $name;

    private bool $active;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private Collection $formats;

    public function __construct()
    {
        $this->formats = new ArrayCollection();

        $this->setActive(true);
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

    public function addFormat(FormatInterface $format): static
    {
        $this->formats->add($format);

        return $this;
    }

    /**
     * @return Collection<int, FormatInterface>
     */
    public function getFormats(): Collection
    {
        return $this->formats;
    }
}
