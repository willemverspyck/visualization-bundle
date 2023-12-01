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
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[OpenApi\Tag(name: 'Menu')]
final class MenuController extends AbstractController
{
    #[Route(path: '/api/menus', name: 'spyck_visualization_menu_list', methods: [Request::METHOD_GET])]
    #[Schema\BadRequest]
    #[Schema\Forbidden]
    #[Schema\NotFound]
    #[Schema\ResponseForList(type: Menu::class, groups: ['spyck:visualization:menu:list'])]
    public function list(MenuRepository $menuRepository, ResponseService $responseService): Response
    {
        $data = $menuRepository->getMenuData();

        return $responseService->getResponseForList($data, null, null, ['spyck:visualization:menu:list']);
    }
}
