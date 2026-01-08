<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Command;

use DateTimeImmutable;
use Exception;
use Spyck\VisualizationBundle\Repository\DownloadRepository;
use Spyck\VisualizationBundle\Service\DownloadService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'spyck:visualization:download', description: 'Command for downloads.')]
final class DownloadCommand
{
    public function __construct(private readonly DownloadService $downloadService, private readonly DownloadRepository $downloadRepository)
    {
    }

    /**
     * @throws Exception
     */
    public function __invoke(SymfonyStyle $style): int
    {
        $style->info('Looking for downloads to execute...');

        $date = new DateTimeImmutable('1 month ago');

        $downloads = $this->downloadRepository->getDownloadsByTimestamp($date, false);

        foreach ($downloads as $download) {
            $this->downloadService->deleteDownload($download);
        }

        $style->success('Done');

        return Command::SUCCESS;
    }
}
