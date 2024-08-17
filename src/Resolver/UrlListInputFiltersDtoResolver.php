<?php

namespace App\Resolver;

use App\Dto\UrlListInputFiltersDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * UrlListInputFiltersDtoResolver class.
 */
class UrlListInputFiltersDtoResolver implements ValueResolverInterface
{
    /**
     * Resolves the value for the URL filter DTO.
     *
     * @param Request          $request  HTTP Request
     * @param ArgumentMetadata $argument Argument metadata
     *
     * @return iterable Iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if (!$argumentType || !is_a($argumentType, UrlListInputFiltersDto::class, true)) {
            return [];
        }

        $tagId = $request->query->get('tagId');

        return [new UrlListInputFiltersDto($tagId)];
    }
}
