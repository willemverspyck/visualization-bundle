<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

interface PreloadMessageInterface
{
    public function getId(): int;

    public function setId(int $id): void;

    public function getVariables(): array;

    public function setVariables(array $variables): void;
}
