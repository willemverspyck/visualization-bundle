<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Entity\Favorite;
use Spyck\VisualizationBundle\Entity\UserInterface;

class FavoriteRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Favorite::class);
    }

    public function putFavorite(UserInterface $user, Dashboard $dashboard): Favorite
    {
        $favorite = new Favorite();
        $favorite->setUser($user);
        $favorite->setDashboard($dashboard);

        $this->getEntityManager()->persist($favorite);
        $this->getEntityManager()->flush();

        return $favorite;
    }
}
