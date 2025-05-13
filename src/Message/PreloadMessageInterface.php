<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

interface PreloadMessageInterface
{
    public function getDashboardId(): int;

    public function setDashboardId(int $dashboardId): void;

    public function getUserId(): ?int;

    public function setUserId(?int $userId): void;

    public function getVariables(): array;

    public function setVariables(array $variables): void;
}
