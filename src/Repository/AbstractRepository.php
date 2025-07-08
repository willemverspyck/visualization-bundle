<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;

abstract class AbstractRepository extends ServiceEntityRepository
{
    protected function patchCollection(Collection $elements, Collection $elementsForPatch): void
    {
        foreach ($elements as $element) {
            if (false === $elementsForPatch->contains($element)) {
                $elements->removeElement($element);
            }
        }

        foreach ($elementsForPatch as $elementForPatch) {
            if (false === $elements->contains($elementForPatch)) {
                $elements->add($elementForPatch);
            }
        }
    }
}
