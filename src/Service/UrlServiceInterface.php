<?php
/**
 * Url service interface.
 */

namespace App\Service;

use App\Dto\UrlListInputFiltersDto;
use App\Entity\Url;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UrlServiceInterface.
 */
interface UrlServiceInterface
{
    /**
     * Save entity.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void;

    /**
     * Delete Url entity.
     *
     * @param Url $url Url entity
     */
    public function delete(Url $url): void;

    /**
     * Generate short url code for new Url entity.
     *
     * @return string $code
     */
    public function generateShortUrlCode(): string;

    /**
     * Find Url entity by shortened Url code.
     *
     * @param string $shortUrl Shortened Url code
     *
     * @return Url|null Url entity
     */
    public function findUrlByShortUrl(string $shortUrl): ?Url;

    /**
     * Get paginated list.
     *
     * @param int  $page   Page number
     * @param User $author Optional author filter
     * @param UrlListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author, UrlListInputFiltersDto $filters): PaginationInterface;



}
