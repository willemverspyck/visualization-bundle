<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Spyck\VisualizationBundle\Entity\Mail;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class MailNormalizer extends AbstractNormalizer
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    /**
     * @param Mail $data
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        $normalize = $this->getNormalize($data, $format, $context);
        $normalize['subscribed'] = null === $user ? null : $data->getUsers()->contains($user);

        return $normalize;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if ($this->isNormalize($data, $format, $context)) {
            return false;
        }

        return $data instanceof Mail;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Mail::class => false,
        ];
    }
}
