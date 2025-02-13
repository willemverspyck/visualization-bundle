<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Spyck\VisualizationBundle\Entity\Download;
use Spyck\VisualizationBundle\Service\DownloadService;

#[AsEntityListener(event: Events::postPersist, entity: Download::class)]
final class DownloadListener
{
    public function __construct(private readonly DownloadService $downloadService)
    {
    }

    public function postPersist(Download $download): void
    {
        $this->downloadService->executeDownloadAsMessage($download);
    }
}
