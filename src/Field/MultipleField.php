<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Field;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Spyck\ApiExtension\Model\Response;
use Spyck\VisualizationBundle\Format\FormatInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

final class MultipleField extends AbstractField implements MultipleFieldInterface
{
    /**
     * @var Collection<int, FieldInterface>
     */
    #[Serializer\Groups(groups: Response::GROUP)]
    private Collection $children;

    public function __construct(string $name)
    {
        parent::__construct();

        $this->children = new ArrayCollection();

        $this->setName($name);
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
}
