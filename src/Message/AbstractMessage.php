<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

abstract class AbstractMessage implements AbstractMessageInterface
{
    private int $id;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
