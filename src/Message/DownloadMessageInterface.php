<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Message;

interface DownloadMessageInterface
{
    public function getId(): int;

    public function setId(int $id): void;
}
