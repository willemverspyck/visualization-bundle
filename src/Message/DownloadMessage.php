<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

final class DownloadMessage implements DownloadMessageInterface
{
    private int $id;
    private int $userId;
    private array $variables;
    private string $view;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
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

    public function getView(): string
    {
        return $this->view;
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }
}
