<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use DateTimeImmutable;
use Exception;
use Spyck\VisualizationBundle\Entity\Download;
use Spyck\VisualizationBundle\Event\DownloadEvent;
use Spyck\VisualizationBundle\Message\DownloadMessage;
use Spyck\VisualizationBundle\Repository\DownloadRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class DownloadService
{
    public function __construct(private readonly DownloadRepository $downloadRepository, private EventDispatcherInterface $eventDispatcher, private readonly MessageBusInterface $messageBus, private readonly ViewService $viewService, private readonly WidgetService $widgetService, #[Autowire(param: 'spyck.visualization.config.download.directory')] private readonly ?string $directory)
    {
    }

    public function executeDownload(Download $download): void
    {
        $timestamp = new DateTimeImmutable();

        $this->downloadRepository->patchDownload(download: $download, fields: ['status', 'duration', 'messages', 'timestamp'], status: Download::STATUS_PENDING, timestamp: $timestamp);

        try {
            $dashboardAsModel = $this->widgetService->getDashboardAsModelById($download->getWidget()->getId(), $download->getVariables());

            $view = $this->viewService->getView($download->getView());

            $file = $this->getFile($download);
            $content = $view->getContent($dashboardAsModel);

            $this->putFile($file, $content);

            $name = $view->getFile($dashboardAsModel->getName(), $dashboardAsModel->getParametersAsStringForSlug());
            $duration = $this->getDuration($timestamp);

            $this->downloadRepository->patchDownload(download: $download, fields: ['name', 'file', 'status', 'duration', 'messages'], name: $name, file: $file, status: Download::STATUS_COMPLETE, duration: $duration);
        } catch (Exception $exception) {
            $duration = $this->getDuration($timestamp);
            $messages = [
                $exception->getMessage(),
            ];

            $this->downloadRepository->patchDownload(download: $download, fields: ['status', 'duration', 'messages'], status: Download::STATUS_ERROR, duration: $duration, messages: $messages);
        }

        $downloadEvent = new DownloadEvent($download);

        $this->eventDispatcher->dispatch($downloadEvent);
    }

    public function executeDownloadAsMessage(Download $download): void
    {
        $downloadMessage = new DownloadMessage();
        $downloadMessage->setId($download->getId());

        $this->messageBus->dispatch($downloadMessage);
    }

    public function getDirectory(): string
    {
        if (null === $this->directory) {
            throw new Exception('Directory not found');
        }

        return $this->directory;
    }

    /**
     * @throws Exception
     */
    public function getFile(Download $download): string
    {
        return md5(sprintf('%s', $download->getId() / $download->getTimestamp()->getTimestamp()));
    }

    public function putFile(string $file, string $content): void
    {
        $filename = sprintf('%s/%s', $this->getDirectory(), $file);

        if (false === file_put_contents($filename, $content)) {
            throw new Exception('Unable to create file');
        }
    }

    private function getDuration(DateTimeImmutable $dateTimeStart): int
    {
        $dateTimeEnd = new DateTimeImmutable();

        return $dateTimeEnd->getTimestamp() - $dateTimeStart->getTimestamp();
    }
}
