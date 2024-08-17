<?php

namespace App\Dto;

use App\Entity\Url;

/**
 * Class UrlListInputFiltersDto.
 */
class UrlListInputFiltersDto
{

    /**
     * Constructor.
     *
     * @param int|null $tagId Tag identifier
     */
    public function __construct(public readonly ?int $tagId = null)
    {
    }
}
