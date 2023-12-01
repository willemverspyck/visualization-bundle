<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends BaseAbstractController
{
    public function getForm(string $type, mixed $object, ?array $data, callable $callback): JsonResponse
    {
        if (null === $data) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm($type, $object);
        $form->submit($data);

        if (false === $form->isValid()) {
            return new JsonResponse($form->getErrors(true), Response::HTTP_OK);
        }

        $data = $callback($form->getData());
        $data['status'] = 'OK';

        return new JsonResponse($data, Response::HTTP_CREATED);
    }
}
