<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

interface MailMessageInterface extends AbstractMessageInterface
{
    public function setUser(int $user): void;

    public function getUser(): int;

    public function setName(string $name): void;

    public function getName(): string;

    public function setDescription(?string $description): void;

    public function getDescription(): ?string;

    public function setVariables(array $variables): void;

    public function getVariables(): array;

    public function setView(string $view): void;

    public function getView(): string;

    public function setMerge(bool $merge): void;

    public function isMerge(): bool;
}
