<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Controller;

use OpenApi\Attributes as OpenApi;
use Spyck\ApiExtension\Schema;
use Spyck\ApiExtension\Service\ResponseService;
use Spyck\VisualizationBundle\Entity\Menu;
use Spyck\VisualizationBundle\Repository\MenuRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[OpenApi\Tag(name: 'Menu')]
final class MenuController extends AbstractController
{
    public const string GROUP_LIST = 'spyck:visualization:menu:list';

    #[Route(path: '/api/menus', name: 'spyck_visualization_menu_list', methods: [Request::METHOD_GET])]
    #[Schema\BadRequest]
    #[Schema\Forbidden]
    #[Schema\NotFound]
    #[Schema\ResponseForList(type: Menu::class, groups: [self::GROUP_LIST])]
    public function list(MenuRepository $menuRepository, ResponseService $responseService): Response
    {
        $menus = $menuRepository->getMenus();

        return $responseService->getResponseForList($menus, null, null, [self::GROUP_LIST]);
    }
}
