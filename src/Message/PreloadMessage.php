<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

final class PreloadMessage implements PreloadMessageInterface
{
    private int $dashboardId;
    private ?int $userId;
    private array $variables;

    public function getDashboardId(): int
    {
        return $this->dashboardId;
    }

    public function setDashboardId(int $dashboardId): void
    {
        $this->dashboardId = $dashboardId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }
}
