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
        return $this->getBookmarkQueryBuilder()
            ->andWhere('bookmark.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, Bookmark>
     *
     * @throws AuthenticationException
     */
    public function getBookmarks(): array
    {
        return $this->getBookmarkQueryBuilder()
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

    private function getBookmarkQueryBuilder(): QueryBuilder
    {
        $user = $this->userService->getUser();

        $queryBuilder = $this->createQueryBuilder('bookmark')
            ->addSelect('dashboard')
            ->innerJoin('bookmark.dashboard', 'dashboard', Join::WITH, 'dashboard.active = TRUE')
            ->innerJoin('dashboard.blocks', 'block', Join::WITH, 'block.active = TRUE')
            ->innerJoin('block.widget', 'widget', Join::WITH, 'widget.active = TRUE');

        if (null === $user) {
            return $queryBuilder
                ->where('bookmark.user IS NULL');
        }

        return $queryBuilder
            ->addSelect('user')
            ->innerJoin('bookmark.user', 'user', Join::WITH, 'user = :user')
            ->innerJoin('widget.group', 'groupRequired', Join::WITH, 'groupRequired MEMBER OF user.groups AND groupRequired.active = TRUE')
            ->setParameter('user', $user);
    }
}
