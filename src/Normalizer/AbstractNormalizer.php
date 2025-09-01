<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractNormalizer implements NormalizerInterface
{
    protected NormalizerInterface $normalizer;

    #[Required]
    public function setNormalizer(#[Autowire(service: 'serializer.normalizer.object')] NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }
}
