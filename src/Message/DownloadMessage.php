<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

final class DownloadMessage implements DownloadMessageInterface
{
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
