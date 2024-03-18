<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Spyck\VisualizationBundle\Entity\Menu;
use Spyck\VisualizationBundle\Repository\MenuRepository;

#[AsEntityListener(event: Events::postPersist, entity: Menu::class)]
#[AsEntityListener(event: Events::postUpdate, entity: Menu::class)]
final class MenuListener
{
    public function __construct(private readonly MenuRepository $menuRepository)
    {
    }

    public function postPersist(Menu $menu): void
    {
        $this->patchPosition($menu->getParent());
    }

    public function postUpdate(Menu $menu): void
    {
        $this->patchPosition($menu->getParent());
    }

    private function patchPosition(?Menu $parent): void
    {
        $position = 1;

        $menus = $this->menuRepository->getMenuDataByParent($parent);

        foreach ($menus as $menu) {
            if ($position !== $menu->getPosition()) {
                $this->menuRepository->patchMenu($menu, ['position'], $position);
            }

            ++$position;
        }
    }
}
