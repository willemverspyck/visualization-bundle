<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

final class PreloadMessage implements PreloadMessageInterface
{
    private int $id;
    private array $variables;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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
