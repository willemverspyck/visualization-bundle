<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

final class MailMessage implements MailMessageInterface
{
    private int $id;
    private int $user;
    private string $name;
    private ?string $description = null;
    private array $variables;
    private string $view;
    private bool $route;
    private bool $merge;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): int
    {
        return $this->user;
    }

    public function setUser(int $user): void
    {
        $this->user = $user;
    }

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

    public function getView(): string
    {
        return $this->view;
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function setRoute(bool $route): void
    {
        $this->route = $route;
    }

    public function hasRoute(): bool
    {
        return $this->route;
    }

    public function setMerge(bool $merge): void
    {
        $this->merge = $merge;
    }

    public function isMerge(): bool
    {
        return $this->merge;
    }
}
