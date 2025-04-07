<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use DateTimeImmutable;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Download;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Spyck\VisualizationBundle\Entity\Widget;
use Spyck\VisualizationBundle\Service\UserService;

class DownloadRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry, private readonly UserService $userService)
    {
        parent::__construct($managerRegistry, Download::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getDownloadById(int $id, bool $authentication = true): ?Download
    {
        return $this->getDownloadsAsQueryBuilder($authentication)
            ->andWhere('download.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getDownloads(bool $authentication = true): array
    {
        return $this->getDownloadsAsQueryBuilder($authentication)
            ->orderBy('download.timestampCreated', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getDownloadsByTimestamp(DateTimeImmutable $timestamp, bool $authentication = true): array
    {
        return $this->getDownloadsAsQueryBuilder($authentication)
            ->andWhere('download.timestamp < :timestamp')
            ->orderBy('download.timestampCreated', 'DESC')
            ->setParameter('timestamp', $timestamp)
            ->getQuery()
            ->getResult();
    }

    public function patchDownload(Download $download, array $fields, ?string $name = null, ?string $file = null, ?string $status = null, ?int $duration = null, ?array $messages = null, ?DateTimeImmutable $timestamp = null): void
    {
        if (in_array('name', $fields, true)) {
            $download->setName($name);
        }

        if (in_array('file', $fields, true)) {
            $download->setFile($file);
        }

        if (in_array('status', $fields, true)) {
            $download->setStatus($status);
        }

        if (in_array('duration', $fields, true)) {
            $download->setDuration($duration);
        }

        if (in_array('messages', $fields, true)) {
            $download->setMessages($messages);
        }

        if (in_array('timestamp', $fields, true)) {
            $download->setTimestamp($timestamp);
        }

        $this->getEntityManager()->persist($download);
        $this->getEntityManager()->flush();
    }

    public function putDownload(UserInterface $user, Widget $widget, array $variables, string $view): Download
    {
        $download = new Download();
        $download->setUser($user);
        $download->setWidget($widget);
        $download->setVariables($variables);
        $download->setView($view);

        $this->getEntityManager()->persist($download);
        $this->getEntityManager()->flush();

        return $download;
    }

    public function removeDownload(Download $download): void
    {
        $this->getEntityManager()->remove($download);
        $this->getEntityManager()->flush();
    }

    private function getDownloadsAsQueryBuilder(bool $authentication = true): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('download')
            ->addSelect('widget')
            ->innerJoin('download.widget', 'widget');

        if (false === $authentication) {
            return $queryBuilder
                ->addSelect('user')
                ->leftJoin('download.user', 'user');
        }

        $user = $this->userService->getUser();

        if (null === $user) {
            return $queryBuilder
                ->where('download.user IS NULL');
        }

        return $queryBuilder
            ->addSelect('user')
            ->innerJoin('download.user', 'user', Join::WITH, 'user = :user')
            ->setParameter('user', $user);
    }
}
