<?php
/**
 * UrlListInputFiltersDto.
 */

namespace App\Dto;

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
