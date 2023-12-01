<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use OpenApi\Attributes as OpenApi;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Filter
{
    #[Serializer\Groups(['spyck:visualization:dashboard:item'])]
    private string $name;

    #[Serializer\Groups(['spyck:visualization:dashboard:item'])]
    private string $type;

    #[Serializer\Groups(['spyck:visualization:dashboard:item'])]
    private string $parameter;

    /**
     * @todo: This must be an object with id, name, parent (array with id, field) and select
     */
    #[OpenApi\Property(type: 'array', items: new OpenApi\Items(type: 'string'))]
    #[Serializer\Groups(['spyck:visualization:dashboard:item'])]
    private array $data;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getParameter(): string
    {
        return $this->parameter;
    }

    public function setParameter(string $parameter): static
    {
        $this->parameter = $parameter;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
