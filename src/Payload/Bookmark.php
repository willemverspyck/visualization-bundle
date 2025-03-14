<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Payload;

use Symfony\Component\Validator\Constraints as Validator;

final class Bookmark
{
    #[Validator\NotNull]
    #[Validator\Type(type: 'string')]
    private string $name;

    #[Validator\NotNull]
    #[Validator\Type(type: 'array')]
    private array $variables;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
}
