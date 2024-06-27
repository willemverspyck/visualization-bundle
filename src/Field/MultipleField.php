<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Field;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Spyck\ApiExtension\Model\Response;
use Spyck\VisualizationBundle\Format\FormatInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

final class MultipleField implements MultipleFieldInterface
{
    #[Serializer\Groups(groups: Response::GROUP)]
    private string $name;

    /**
     * @var Collection<int, FieldInterface>
     */
    #[Serializer\Groups(groups: Response::GROUP)]
    private Collection $children;

    #[Serializer\Groups(groups: Response::GROUP)]
    private Collection $formats;

    public function __construct(string $name)
    {
        $this->children = new ArrayCollection();
        $this->formats = new ArrayCollection();

        $this->setName($name);
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

    public function addChild(FieldInterface $child): static
    {
        $child->setParent($this);

        $this->children->add($child);

        return $this;
    }

    /**
     * @return Collection<int, FieldInterface>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function removeChild(FieldInterface $child): void
    {
        $this->children->removeElement($child);
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
