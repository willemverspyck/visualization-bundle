<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Payload;

use Symfony\Component\Validator\Constraints as Validator;

final class Mail
{
    #[Validator\NotNull]
    private string $name;

    #[Validator\Type(type: 'string')]
    private ?string $description = null;

    #[Validator\NotNull]
    #[Validator\Type(type: 'array')]
    private array $variables;

    #[Validator\Type(type: 'string')]
    private ?string $view = null;

    #[Validator\NotNull]
    #[Validator\Type(type: 'boolean')]
    private bool $route;

    #[Validator\NotNull]
    #[Validator\Type(type: 'boolean')]
    private bool $inline;

    #[Validator\NotNull]
    #[Validator\Type(type: 'boolean')]
    private bool $merge;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }

    public function getView(): ?string
    {
        return $this->view;
    }

    public function setView(?string $view): void
    {
        $this->view = $view;
    }

    public function hasRoute(): bool
    {
        return $this->route;
    }

    public function setRoute(bool $route): void
    {
        $this->route = $route;
    }

    public function isInline(): bool
    {
        return $this->inline;
    }

    public function setInline(bool $inline): void
    {
        $this->inline = $inline;
    }

    public function isMerge(): bool
    {
        return $this->merge;
    }

    public function setMerge(bool $merge): void
    {
        $this->merge = $merge;
    }
}
