<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event;

use Spyck\VisualizationBundle\Entity\Download;
use Spyck\VisualizationBundle\Entity\Widget;
use Symfony\Contracts\EventDispatcher\Event;

final class DownloadEvent extends Event
{
    public function __construct(private readonly Download $download)
    {
    }

    public function getDownload(): Download
    {
        return $this->download;
    }
}
