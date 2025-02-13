<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

interface MailMessageInterface
{
    public function getDashboardId(): int;

    public function setDashboardId(int $dashboardId): void;

    public function getUserId(): int;

    public function setUserId(int $userId): void;

    public function getName(): string;

    public function setName(string $name): void;

    public function getDescription(): ?string;

    public function setDescription(?string $description): void;

    public function getVariables(): array;

    public function setVariables(array $variables): void;

    public function getView(): ?string;

    public function setView(?string $view): void;

    public function hasRoute(): bool;

    public function setRoute(bool $route): void;

    public function isInline(): bool;

    public function setInline(bool $inline): void;

    public function isMerge(): bool;

    public function setMerge(bool $merge): void;
}
