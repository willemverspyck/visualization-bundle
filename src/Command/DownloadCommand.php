<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Command;

use DateTimeImmutable;
use Exception;
use Spyck\VisualizationBundle\Repository\DownloadRepository;
use Spyck\VisualizationBundle\Service\DownloadService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'spyck:visualization:download', description: 'Command for downloads.')]
final class DownloadCommand extends Command
{
    public function __construct(private readonly DownloadService $downloadService, private readonly DownloadRepository $downloadRepository)
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new DateTimeImmutable('1 month ago');

        $filesystem = new Filesystem();

        $downloads = $this->downloadRepository->getDownloadsByTimestamp($date, false);

        foreach ($downloads as $download) {
            $file = sprintf('%s/%s', $this->downloadService->getDirectory(), $download->getFile());

            if ($filesystem->exists($file)) {
                $filesystem->remove($file);
            }

            $this->downloadRepository->removeDownload($download);
        }

        return Command::SUCCESS;
    }
}
