<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Spyck\VisualizationBundle\Entity\Menu;

#[AsEntityListener(event: Events::prePersist, entity: Menu::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Menu::class)]
final class MenuListener
{
    public function prePersist(Menu $menu): void
    {
        $this->patchPosition($menu);
    }

    public function preUpdate(Menu $menu): void
    {
        $this->patchPosition($menu, true);
    }

    private function patchPosition(Menu $menu): void
    {
        $parent = $menu->getParent();

        if (null === $parent) {
            return;
        }

        // Reverse the array so that the position of the menu element replaces the existing element at that position.
        $children = array_reverse($parent->getChildren()->toArray());

        usort($children, function (Menu $a, Menu $b): int {
            if ($a->getPosition() === $b->getPosition()) {
                return 0;
            }

            return $a->getPosition() < $b->getPosition() ? -1 : 1;
        });

        foreach ($children as $position => $child) {
            $child->setPosition($position + 1);
        }
    }
}
