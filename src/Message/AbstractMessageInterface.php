<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

interface AbstractMessageInterface
{
    public function setId(int $id): void;

    public function getId(): int;
}
