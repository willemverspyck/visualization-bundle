<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Controller;

use OpenApi\Attributes as OpenApi;
use Spyck\ApiExtension\Schema;
use Spyck\ApiExtension\Service\ResponseService;
use Spyck\VisualizationBundle\Entity\Download;
use Spyck\VisualizationBundle\Payload\Download as DownloadAsPayload;
use Spyck\VisualizationBundle\Repository\DownloadRepository;
use Spyck\VisualizationBundle\Repository\WidgetRepository;
use Spyck\VisualizationBundle\Service\DownloadService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
#[OpenApi\Tag(name: 'Downloads')]
final class DownloadController extends AbstractController
{
    public const string GROUP_LIST = 'spyck:visualization:download:list';
    public const string GROUP_WIDGET = 'spyck:visualization:download:widget';

    #[Route(path: '/api/download/{downloadId}', name: 'spyck_visualization_download_item', requirements: ['downloadId' => Requirement::DIGITS], methods: [Request::METHOD_GET])]
    public function item(DownloadRepository $downloadRepository, DownloadService $downloadService, int $downloadId): Response
    {
        $download = $downloadRepository->getDownloadById($downloadId);

        if (null === $download) {
            throw new $this->createNotFoundException('Download not found');
        }

        $contentDisposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $download->getName(),
        );

        return new BinaryFileResponse(file: sprintf('%s/%s', $downloadService->getDirectory(), $download->getFile()), headers: [
            'Content-Disposition' => $contentDisposition,
        ]);
    }

    #[Route(path: '/api/downloads', name: 'spyck_visualization_download_list', methods: [Request::METHOD_GET])]
    #[Schema\BadRequest]
    #[Schema\Forbidden]
    #[Schema\NotFound]
    #[Schema\ResponseForList(type: Download::class, groups: [self::GROUP_LIST])]
    public function list(DownloadRepository $downloadRepository, ResponseService $responseService): Response
    {
        $downloads = $downloadRepository->getDownloads();

        return $responseService->getResponseForList(data: $downloads, groups: [self::GROUP_LIST]);
    }

    #[Route(path: '/api/download/widget/{widgetId}', name: 'spyck_visualization_download_widget', requirements: ['widgetId' => Requirement::DIGITS], methods: [Request::METHOD_POST])]
    #[Schema\BadRequest]
    #[Schema\Forbidden]
    #[Schema\NotFound]
    #[Schema\ResponseForItem(type: Download::class, groups: [self::GROUP_WIDGET])]
    public function widget(DownloadRepository $downloadRepository, ResponseService $responseService, TokenStorageInterface $tokenStorage, WidgetRepository $widgetRepository, #[MapRequestPayload] DownloadAsPayload $downloadAsPayload, int $widgetId): Response
    {
        $user = $tokenStorage->getToken()?->getUser();

        if (null === $user) {
            throw $this->createAccessDeniedException();
        }

        $widget = $widgetRepository->getWidgetById($widgetId);

        if (null === $widget) {
            throw $this->createNotFoundException('Widget not found');
        }

        $download = $downloadRepository->putDownload(user: $user, widget: $widget, variables: $downloadAsPayload->getVariables(), view: $downloadAsPayload->getView());

        return $responseService->getResponseForItem(data: $download, groups: [self::GROUP_WIDGET]);
    }
}
