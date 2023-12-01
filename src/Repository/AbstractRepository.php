<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Spyck\VisualizationBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

abstract class AbstractRepository extends ServiceEntityRepository
{
    /**
     * @throws AuthenticationException
     */
    protected function getUserByToken(?TokenInterface $token): ?UserInterface
    {
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();

        if (null === $user) {
            throw new AuthenticationException('User not found');
        }

        return $user;
    }

    /**
     * Truncate field if the length is too long.
     */
    protected function truncate(string $className, string $field, ?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $metaData = $this->getEntityManager()->getClassMetadata($className);

        if (array_key_exists($field, $metaData->fieldMappings)) {
            $length = $metaData->fieldMappings[$field]['length'];

            if (mb_strlen($value) > $length) {
                $value = mb_substr($value, 0, $length);
            }
        }

        return $value;
    }
}
