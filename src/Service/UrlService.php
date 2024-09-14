<?php
/**
 * Url service.
 */

namespace App\Service;

use App\Dto\UrlListFiltersDto;
use App\Dto\UrlListInputFiltersDto;
use App\Entity\Url;
use App\Entity\User;
use App\Repository\UrlRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class UrlService.
 */
class UrlService implements UrlServiceInterface
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 3;

    /**
     * Constructor.
     *
     * @param UrlRepository       $urlRepository URL repository
     * @param TagServiceInterface $tagService    Tag service
     * @param PaginatorInterface  $paginator     Paginator
     */
    public function __construct(private readonly UrlRepository $urlRepository, private readonly TagServiceInterface $tagService, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int                    $page    Page number
     * @param User|null              $author  Optional author filter
     * @param UrlListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface<string, mixed> Paginated list
     *
     * @throws NonUniqueResultException
     */
    public function getPaginatedList(int $page, ?User $author, UrlListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);
        $queryBuilder = $author ? $this->urlRepository->queryByAuthor($author, $filters) : $this->urlRepository->queryAll($filters);

        $queryBuilder->leftJoin('url.clicks', 'click')
            ->addSelect('COUNT(click.id) AS clickCount')
            ->groupBy('url.id')
            ->orderBy('url.createdAt', 'DESC'); // Ensure ordering as needed

        return $this->paginator->paginate(
            $queryBuilder,
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void
    {
        if (null === $url->getShortUrl()) {
            $url->setShortUrl($this->generateShortUrlCode($url->getLongUrl()));
        }
        $this->urlRepository->save($url);
    }

    /**
     * Delete Url entity.
     *
     * @param Url $url Url entity
     */
    public function delete(Url $url): void
    {
        $this->urlRepository->delete($url);
    }

    /**
     * Find Url entity by shortened Url code.
     *
     * @param string $shortUrl Shortened Url code
     *
     * @return Url|null Url entity
     */
    public function findOneByShortUrl(string $shortUrl): ?Url
    {
        return $this->urlRepository->findOneByShortUrl($shortUrl);
    }

    /**
     * Find Url entity by Id.
     *
     * @param string $id Id
     *
     * @return Url|null Url entity
     */
    public function findOneById(string $id): ?Url
    {
        return $this->urlRepository->findOneById($id);
    }

    /**
     * Generate 7 characters short url code for new Url entity.
     *
     * @param string $longUrl Long url code
     *
     * @return string Short url code
     */
    public function generateShortUrlCode(string $longUrl): string
    {
        $hash = md5($longUrl); // Generate a hash
        $base62 = $this->base62Encode(hexdec(substr($hash, 0, 15))); // Convert to base62

        // Ensure the code is exactly 7 characters long
        $shortCode = substr($base62, 0, 7);

        // Check for uniqueness in the database
        while (null !== $this->findOneByShortUrl($shortCode)) {
            $shortCode = $this->generateRandomBase62Code();
        }

        return $shortCode;
    }

    /**
     * Prepare filters for the URLs list.
     *
     * @param UrlListInputFiltersDto $filters Raw filters from the request
     *
     * @return UrlListFiltersDto Result filters
     *
     * @throws NonUniqueResultException
     */
    private function prepareFilters(UrlListInputFiltersDto $filters): UrlListFiltersDto
    {
        return new UrlListFiltersDto(
            null !== $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null,
        );
    }


    /**
     * Encode number to base 62 string format.
     *
     * @param int $num Number to be converted
     *
     * @return string Encoded string
     */
    private function base62Encode(int $num): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($characters);
        $encoded = '';

        while ($num > 0) {
            $encoded = $characters[$num % $base].$encoded;
            $num = (int) ($num / $base);
        }

        return $encoded;
    }

    /**
     * Generate random 7 characters Base62 string.
     *
     * @return string Random string
     */
    private function generateRandomBase62Code(): string
    {
        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 7);
    }
}
