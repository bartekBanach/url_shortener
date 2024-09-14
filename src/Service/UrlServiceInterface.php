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
     * Generate short URL code for new Url entity.
     *
     * @param string $longUrl Long URL code
     *
     * @return string Short URL code
     */
    public function generateShortUrlCode(string $longUrl): string;

    /**
     * Find Url entity by shortened URL code.
     *
     * @param string $shortUrl Shortened URL code
     *
     * @return Url|null Url entity
     */
    public function findOneByShortUrl(string $shortUrl): ?Url;

    /**
     * Find Url entity by Id.
     *
     * @param string $id Id
     *
     * @return Url|null Url entity
     */
    public function findOneById(string $id): ?Url;

    /**
     * Get paginated list.
     *
     * @param int                    $page    Page number
     * @param User|null              $author  Optional author filter
     * @param UrlListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, ?User $author, UrlListInputFiltersDto $filters): PaginationInterface;
}
