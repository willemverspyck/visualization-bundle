<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Controller;

use Exception;
use OpenApi\Attributes as OpenApi;
use Spyck\ApiExtension\Schema;
use Spyck\ApiExtension\Service\ResponseService;
use Spyck\VisualizationBundle\Entity\Menu;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Spyck\VisualizationBundle\Payload\Bookmark as BookmarkAsPayload;
use Spyck\VisualizationBundle\Repository\BookmarkRepository;
use Spyck\VisualizationBundle\Repository\DashboardRepository;
use Spyck\VisualizationBundle\Service\DashboardService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
    public function list(BookmarkRepository $bookmarkRepository, ResponseService $responseService): Response
    {
        $bookmarks = $bookmarkRepository->getBookmarks();

        return $responseService->getResponseForList(data: $bookmarks, groups: [self::GROUP_LIST]);
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/api/bookmark/dashboard/{dashboardId}', name: 'spyck_visualization_bookmark_dashboard', requirements: ['dashboardId' => Requirement::DIGITS], methods: [Request::METHOD_POST])]
    public function dashboard(BookmarkRepository $bookmarkRepository, DashboardRepository $dashboardRepository, DashboardService $dashboardService, ResponseService $responseService, TokenStorageInterface $tokenStorage, #[MapRequestPayload] BookmarkAsPayload $bookmarkAsPayload, int $dashboardId): Response
    {
        $dashboard = $dashboardRepository->getDashboardById($dashboardId);

        if (null === $dashboard) {
            return $responseService->getResponseForItem();
        }

        $requests = $dashboardService->validateParameters($dashboard, $bookmarkAsPayload->getVariables());

        if (null !== $requests) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        /** @var UserInterface $user */
        $user = $tokenStorage->getToken()?->getUser();

        if (null === $user) {
            throw $this->createAccessDeniedException();
        }

        $bookmarkRepository->putBookmark(user: $user, dashboard: $dashboard, name: $bookmarkAsPayload->getName(), variables: $bookmarkAsPayload->getVariables());

        $data = [
            'status' => 'OK',
        ];

        return new JsonResponse(data: $data);
    }

    #[Route(path: '/api/bookmark/{bookmarkId}', name: 'spyck_visualization_bookmark_delete', requirements: ['bookmarkId' => Requirement::DIGITS], methods: [Request::METHOD_DELETE])]
    public function delete(BookmarkRepository $bookmarkRepository, ResponseService $responseService, TokenStorageInterface $tokenStorage, int $bookmarkId): Response
    {
        $user = $tokenStorage->getToken()?->getUser();

        if (null === $user) {
            throw $this->createAccessDeniedException();
        }

        $bookmark = $bookmarkRepository->getBookmarkById($bookmarkId);

        if (null === $bookmark) {
            return $responseService->getResponseForItem();
        }

        $bookmarkRepository->deleteBookmark(bookmark: $bookmark);

        return $responseService->getResponseForItem();
    }
}
