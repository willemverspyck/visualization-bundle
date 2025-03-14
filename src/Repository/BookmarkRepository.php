<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Bookmark;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class BookmarkRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry, private readonly TokenStorageInterface $tokenStorage)
    {
        parent::__construct($managerRegistry, Bookmark::class);
    }

    /**
     * @throws AuthenticationException
     */
    public function getBookmarkById(int $id): ?Bookmark
    {
        $user = $this->getUserByToken($this->tokenStorage->getToken());

        if (null === $user) {
            return null;
        }

        return $this->createQueryBuilder('bookmark')
            ->innerJoin('bookmark.user', 'user', Join::WITH, 'user = :user')
            ->where('bookmark.id = :id')
            ->setParameter('user', $user)
            ->setParameter('id', $id)
            ->getQuery()
            ->useQueryCache(true)
            ->getResult();
    }

    /**
     * @return array<int, Bookmark>
     *
     * @throws AuthenticationException
     */
    public function getBookmarks(): array
    {
        $user = $this->getUserByToken($this->tokenStorage->getToken());

        if (null === $user) {
            return [];
        }

        return $this->createQueryBuilder('bookmark')
            ->addSelect('dashboard')
            ->innerJoin('bookmark.user', 'user', Join::WITH, 'user = :user')
            ->innerJoin('bookmark.dashboard', 'dashboard', Join::WITH, 'dashboard.active = TRUE')
            ->innerJoin('dashboard.blocks', 'block', Join::WITH, 'block.active = TRUE')
            ->innerJoin('block.widget', 'widget', Join::WITH, 'widget.active = TRUE')
            ->innerJoin('widget.group', 'groupRequired', Join::WITH, 'groupRequired IN (:groups) AND groupRequired.active = TRUE')
            ->orderBy('bookmark.timestampCreated', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('groups', $user->getGroups())
            ->getQuery()
            ->useQueryCache(true)
            ->getResult();
    }

    public function deleteBookmark(Bookmark $bookmark): void
    {
        $this->getEntityManager()->remove($bookmark);
        $this->getEntityManager()->flush();
    }

    public function putBookmark(UserInterface $user, Dashboard $dashboard, string $name, array $variables): Bookmark
    {
        $bookmark = new Bookmark();
        $bookmark->setUser($user);
        $bookmark->setDashboard($dashboard);
        $bookmark->setName($name);
        $bookmark->setVariables($variables);

        $this->getEntityManager()->persist($bookmark);
        $this->getEntityManager()->flush();

        return $bookmark;
    }
}
