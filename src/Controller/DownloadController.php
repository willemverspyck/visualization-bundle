<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Controller;

use OpenApi\Attributes as OpenApi;
use Spyck\ApiExtension\Schema;
use Spyck\ApiExtension\Service\ResponseService;
use Spyck\VisualizationBundle\Entity\Download;
use Spyck\VisualizationBundle\Map\DownloadMap;
use Spyck\VisualizationBundle\Payload\Download as DownloadAsPayload;
use Spyck\VisualizationBundle\Repository\DownloadRepository;
use Spyck\VisualizationBundle\Repository\WidgetRepository;
use Spyck\VisualizationBundle\Service\DownloadService;
use Spyck\VisualizationBundle\Service\UserService;
use Spyck\VisualizationBundle\Service\ViewService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[AsController]
#[OpenApi\Tag(name: 'Downloads')]
final class DownloadController extends AbstractController
{
    public const string GROUP_LIST = 'spyck:visualization:download:list';
    public const string GROUP_WIDGET = 'spyck:visualization:download:widget';

    #[Route(path: '/api/download/{downloadId}', name: 'spyck_visualization_download_item', requirements: ['downloadId' => Requirement::DIGITS], methods: [Request::METHOD_GET])]
    public function item(DownloadRepository $downloadRepository, DownloadService $downloadService, ViewService $viewService, int $downloadId): Response
    {
        $download = $downloadRepository->getDownloadById($downloadId);

        if (null === $download) {
            throw $this->createNotFoundException('Download not found');
        }

        if (null === $download->getFile()) {
            throw $this->createNotFoundException('File not found');
        }

        if (Download::STATUS_COMPLETE !== $download->getStatus()) {
            throw $this->createNotFoundException('Download not completed');
        }

        $view = $viewService->getView($download->getView());

        $file = $view->getFile($download->getName());

        $contentDisposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $file);

        return new BinaryFileResponse(file: sprintf('%s/%s', $downloadService->getDirectory(), $download->getFile()), headers: [
            'Content-Disposition' => $contentDisposition,
            'Content-Type' => $view::getContentType(),
        ]);
    }

    #[Route(path: '/api/downloads', name: 'spyck_visualization_download_list', methods: [Request::METHOD_GET])]
    #[Schema\BadRequest]
    #[Schema\Forbidden]
    #[Schema\NotFound]
    #[Schema\ResponseForList(type: Download::class, groups: [self::GROUP_LIST])]
    public function list(DownloadRepository $downloadRepository, ResponseService $responseService, #[MapQueryString] DownloadMap $downloadMap = new DownloadMap()): Response
    {
        $downloads = $downloadRepository->getDownloadsByMapAsQueryBuilder($downloadMap);

        return $responseService->getResponseForList(data: $downloads, map: $downloadMap, groups: [self::GROUP_LIST]);
    }

    #[Route(path: '/api/download/widget/{widgetId}', name: 'spyck_visualization_download_widget', requirements: ['widgetId' => Requirement::DIGITS], methods: [Request::METHOD_POST])]
    #[Schema\BadRequest]
    #[Schema\Forbidden]
    #[Schema\NotFound]
    #[Schema\ResponseForItem(type: Download::class, groups: [self::GROUP_WIDGET])]
    public function widget(DownloadRepository $downloadRepository, ResponseService $responseService, UserService $userService, WidgetRepository $widgetRepository, #[MapRequestPayload] DownloadAsPayload $downloadAsPayload, int $widgetId): Response
    {
        $user = $userService->getUser();

        $widget = $widgetRepository->getWidgetById($widgetId);

        if (null === $widget) {
            throw $this->createNotFoundException('Widget not found');
        }

        $download = $downloadRepository->putDownload(user: $user, widget: $widget, name: $downloadAsPayload->getName(), variables: $downloadAsPayload->getVariables(), view: $downloadAsPayload->getView());

        return $responseService->getResponseForItem(data: $download, groups: [self::GROUP_WIDGET]);
    }
}
