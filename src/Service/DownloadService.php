<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use DateTimeImmutable;
use Exception;
use Spyck\VisualizationBundle\Entity\Download;
use Spyck\VisualizationBundle\Message\DownloadMessage;
use Spyck\VisualizationBundle\Repository\DownloadRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class DownloadService
{
    public function __construct(private readonly DownloadRepository $downloadRepository, private readonly MessageBusInterface $messageBus, private readonly ViewService $viewService, private readonly WidgetService $widgetService, #[Autowire(param: 'spyck.visualization.config.download.directory')] private readonly ?string $directory)
    {
    }

    public function executeDownload(Download $download): void
    {
        $timestamp = new DateTimeImmutable();

        $this->downloadRepository->patchDownload(download: $download, fields: ['status', 'duration', 'messages', 'timestamp'], status: Download::STATUS_PENDING, timestamp: $timestamp);

        $dashboardAsModel = $this->widgetService->getDashboardAsModelById($download->getWidget()->getId(), $download->getVariables());

        $content = $this->viewService->getView($download->getView())->getContent($dashboardAsModel);

        file_put_contents($this->getFile($download), $content);

        $duration = $this->getDuration($timestamp);

        $this->downloadRepository->patchDownload(download: $download, fields: ['status', 'duration'], status: Download::STATUS_COMPLETE, duration: $duration);
    }

    public function executeDownloadAsMessage(Download $download): void
    {
        $downloadMessage = new DownloadMessage();
        $downloadMessage->setId($download->getId());
        $downloadMessage->setUserId($download->getUser()->getId());
        $downloadMessage->setView($download->getView());
        $downloadMessage->setVariables($download->getVariables());

        $this->messageBus->dispatch($downloadMessage);
    }

    /**
     * @throws Exception
     */
    public function getFile(Download $download): string
    {
        if (null === $this->directory) {
            throw new Exception('Directory not found');
        }

        return sprintf('%s/%s', $this->directory, md5(sprintf('%s', $download->getId() + $download->getTimestamp()->getTimestamp())));
    }

    private function getDuration(DateTimeImmutable $dateTimeStart): int
    {
        $dateTimeEnd = new DateTimeImmutable();

        return $dateTimeEnd->getTimestamp() - $dateTimeStart->getTimestamp();
    }
}
