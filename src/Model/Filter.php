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
    private string $field;

    #[Serializer\Groups(['spyck:visualization:dashboard:item'])]
    private ?array $config = null;

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

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getConfig(): ?array
    {
        return $this->config;
    }

    public function setConfig(?array $config): static
    {
        $this->config = $config;

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
