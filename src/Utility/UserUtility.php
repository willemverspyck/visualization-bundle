<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Utility;

use Spyck\VisualizationBundle\Entity\UserInterface;
use Symfony\Component\Mime\Address;

final class UserUtility
{
    public static function getAddress(UserInterface $user): Address
    {
        return new Address($user->getEmail(), null === $user->getName() ? '' : $user->getName());
    }
}
