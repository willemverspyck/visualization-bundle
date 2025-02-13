<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

final class MailMessage implements MailMessageInterface
{
    private int $dashboardId;
    private int $userId;
    private string $name;
    private ?string $description = null;
    private array $variables;
    private ?string $view = null;
    private bool $route;
    private bool $inline;
    private bool $merge;

    public function getDashboardId(): int
    {
        return $this->dashboardId;
    }

    public function setDashboardId(int $dashboardId): void
    {
        $this->dashboardId = $dashboardId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
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
