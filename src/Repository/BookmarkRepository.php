<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Bookmark;
use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Spyck\VisualizationBundle\Service\UserService;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class BookmarkRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry, private readonly UserService $userService)
    {
        parent::__construct($managerRegistry, Bookmark::class);
    }

    /**
     * @throws AuthenticationException
     */
    public function getBookmarkById(int $id): ?Bookmark
    {
        return $this->getBookmarksAsQueryBuilder()
            ->andWhere('bookmark.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<int, Bookmark>
     *
     * @throws AuthenticationException
     */
    public function getBookmarks(): array
    {
        return $this->getBookmarksAsQueryBuilder()
            ->orderBy('bookmark.timestampCreated', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function deleteBookmark(Bookmark $bookmark): void
    {
        $this->getEntityManager()->remove($bookmark);
        $this->getEntityManager()->flush();
    }

    public function putBookmark(?UserInterface $user, Dashboard $dashboard, string $name, array $variables): Bookmark
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

    private function getBookmarksAsQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('bookmark')
            ->addSelect('dashboard')
            ->innerJoin('bookmark.dashboard', 'dashboard');

        $user = $this->userService->getUser();

        if (null === $user) {
            return $queryBuilder
                ->where('bookmark.user IS NULL');
        }

        return $queryBuilder
            ->addSelect('user')
            ->innerJoin('bookmark.user', 'user', Join::WITH, 'user = :user')
            ->setParameter('user', $user);
    }
}
