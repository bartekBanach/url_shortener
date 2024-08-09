<?php
/**
 * Url service.
 */

namespace App\Service;

use App\Entity\Url;
use App\Repository\UrlRepository;
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
     * @param UrlRepository      $urlRepository URL repository
     * @param PaginatorInterface $paginator     Paginator
     */
    public function __construct(private readonly UrlRepository $urlRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlRepository->queryAll(),
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
        $this->urlRepository->save($url);

        if (null === $url->getShortUrl()) {
            $url->setShortUrl($url->getId());
            $this->urlRepository->save($url);
        }
    }


    /**
     * Generate short url code for new Url entity.
     *
     * @return string $code
     */
    public function generateShortUrlCode(): string
    {
        return bin2hex(random_bytes(6));
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
    public function findUrlByShortUrl(string $shortUrl): ?Url
    {
        return $this->urlRepository->findOneBy(['shortUrl' => $shortUrl]);
    }
}
