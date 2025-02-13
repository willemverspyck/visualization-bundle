<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

interface DownloadMessageInterface
{
    public function getId(): int;

    public function setId(int $id): void;

    public function getUserId(): int;

    public function setUserId(int $userId): void;

    public function getVariables(): array;

    public function setVariables(array $variables): void;

    public function getView(): string;

    public function setView(string $view): void;
}
