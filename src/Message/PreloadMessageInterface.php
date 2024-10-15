<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

interface PreloadMessageInterface
{
    public function setId(int $id): void;

    public function getId(): int;

    public function setVariables(array $variables): void;

    public function getVariables(): array;
}
