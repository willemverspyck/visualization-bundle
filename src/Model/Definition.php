<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Spyck\ApiExtension\Model\ConfigInterface;
use Spyck\ApiExtension\Model\Response;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Definition implements ConfigInterface
{
    #[Serializer\Groups(groups: Response::GROUP)]
    private string $name;

    #[Serializer\Groups(groups: Response::GROUP)]
    private ?string $description = null;

    private array $parameters = [];

    /**
     * @var ArrayCollection<int, Field>
     */
    #[Serializer\Groups(groups: Response::GROUP)]
    private ArrayCollection $fields;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
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

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function addField(Field $field): static
    {
        $this->fields->add($field);

        return $this;
    }

    public function clearFields(): void
    {
        $this->fields->clear();
    }

    /**
     * @return ArrayCollection<int, Field>
     */
    public function getFields(): ArrayCollection
    {
        return $this->fields;
    }

    public function removeField(Field $field): void
    {
        $this->fields->removeElement($field);
    }
}
