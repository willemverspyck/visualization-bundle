<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use DateTimeImmutable;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\VisualizationBundle\Entity\Download;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Spyck\VisualizationBundle\Entity\Widget;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DownloadRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $managerRegistry, private readonly TokenStorageInterface $tokenStorage)
    {
        parent::__construct($managerRegistry, Download::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getDownloadById(int $id): ?Download
    {
        $user = $this->getUserByToken($this->tokenStorage->getToken());

        if (null === $user) {
            return null;
        }

        return $this->createQueryBuilder('download')
            ->addSelect('user')
            ->addSelect('widget')
            ->innerJoin('download.user', 'user', Join::WITH, 'user = :user')
            ->innerJoin('download.widget', 'widget', Join::WITH, 'widget.active = TRUE')
            ->innerJoin('widget.group', 'groupRequired', Join::WITH, 'groupRequired IN (:groups) AND groupRequired.active = TRUE')
            ->where('download.id = :id')
            ->orderBy('download.timestampCreated', 'DESC')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->setParameter('groups', $user->getGroups())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getDownloads(): array
    {
        $user = $this->getUserByToken($this->tokenStorage->getToken());

        if (null === $user) {
            return [];
        }

        return $this->createQueryBuilder('download')
            ->addSelect('user')
            ->addSelect('widget')
            ->innerJoin('download.user', 'user', Join::WITH, 'user = :user')
            ->innerJoin('download.widget', 'widget', Join::WITH, 'widget.active = TRUE')
            ->innerJoin('widget.group', 'groupRequired', Join::WITH, 'groupRequired IN (:groups) AND groupRequired.active = TRUE')
            ->orderBy('download.timestampCreated', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('groups', $user->getGroups())
            ->getQuery()
            ->getResult();
    }

    public function patchDownload(Download $download, array $fields, ?string $status = null, ?int $duration = null, ?array $messages = null, ?DateTimeImmutable $timestamp = null): void
    {
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
}
