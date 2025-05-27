<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Controller;

use Exception;
use OpenApi\Attributes as OpenApi;
use Spyck\ApiExtension\Schema;
use Spyck\ApiExtension\Service\ResponseService;
use Spyck\VisualizationBundle\Entity\Menu;
use Spyck\VisualizationBundle\Map\BookmarkMap;
use Spyck\VisualizationBundle\Payload\Bookmark as BookmarkAsPayload;
use Spyck\VisualizationBundle\Repository\BookmarkRepository;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Spyck\VisualizationBundle\Service\DashboardService;
use Spyck\VisualizationBundle\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[AsController]
#[OpenApi\Tag(name: 'Bookmarks')]
final class BookmarkController extends AbstractController
{
    public const string GROUP_LIST = 'spyck:visualization:bookmark:list';

    #[Route(path: '/api/bookmarks', name: 'spyck_visualization_bookmark_list', methods: [Request::METHOD_GET])]
    #[Schema\BadRequest]
    #[Schema\Forbidden]
    #[Schema\NotFound]
    #[Schema\ResponseForList(type: Menu::class, groups: [self::GROUP_LIST])]
    public function list(BookmarkRepository $bookmarkRepository, ResponseService $responseService, #[MapQueryString] BookmarkMap $bookmarkMap = new BookmarkMap()): Response
    {
        $bookmarks = $bookmarkRepository->getBookmarksByMapAsQueryBuilder($bookmarkMap);

        return $responseService->getResponseForList(data: $bookmarks, map: $bookmarkMap, groups: [self::GROUP_LIST]);
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/api/bookmark/dashboard/{dashboardId}', name: 'spyck_visualization_bookmark_dashboard', requirements: ['dashboardId' => Requirement::DIGITS], methods: [Request::METHOD_POST])]
    public function dashboard(BookmarkRepository $bookmarkRepository, DashboardRepository $dashboardRepository, DashboardService $dashboardService, ResponseService $responseService, UserService $userService, #[MapRequestPayload] BookmarkAsPayload $bookmarkAsPayload, int $dashboardId): Response
    {
        $dashboard = $dashboardRepository->getDashboardById($dashboardId);

        if (null === $dashboard) {
            return $responseService->getResponseForItem();
        }

        $user = $userService->getUser();
        $name = $bookmarkAsPayload->getName();
        $variables = $dashboardService->getVariables($dashboard, $bookmarkAsPayload->getVariables(), false);

        $bookmarkRepository->putBookmark(user: $user, dashboard: $dashboard, name: $name, variables: $variables);

        $data = [
            'status' => 'OK',
        ];

        return new JsonResponse(data: $data);
    }

    #[Route(path: '/api/bookmark/{bookmarkId}', name: 'spyck_visualization_bookmark_delete', requirements: ['bookmarkId' => Requirement::DIGITS], methods: [Request::METHOD_DELETE])]
    public function delete(BookmarkRepository $bookmarkRepository, ResponseService $responseService, int $bookmarkId): Response
    {
        $bookmark = $bookmarkRepository->getBookmarkById($bookmarkId);

        if (null === $bookmark) {
            return $responseService->getResponseForItem();
        }

        $bookmarkRepository->deleteBookmark(bookmark: $bookmark);

        return new JsonResponse(data: ['OK']);
    }
}
