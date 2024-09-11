<?php
/**
 * Click service.
 */

namespace App\Service;

use App\Entity\Click;
use App\Entity\Url;
use App\Repository\ClickRepository;

/**
 * Class ClickService.
 *
 * Service for managing Click entities.
 */
class ClickService implements ClickServiceInterface
{
    /**
     * Constructor.
     *
     * @param ClickRepository $clickRepository Click repository
     */
    public function __construct(private readonly ClickRepository $clickRepository)
    {
    }

    /**
     * Save a click entity.
     *
     * @param Click $click Click entity to be saved
     */
    public function save(Click $click): void
    {
        $this->clickRepository->save($click);
    }

    /**
     * Get clicks by URL.
     *
     * Retrieves all clicks associated with a specific URL.
     *
     * @param Url $url The URL entity to retrieve clicks for
     *
     * @return Click[] List of Click entities associated with the URL
     */
    public function getClicksByUrl(Url $url): array
    {
        return $this->clickRepository->findBy(['url' => $url]);
    }

    /**
     * Get the count of clicks by URL.
     *
     * Retrieves the total number of clicks associated with a specific URL.
     *
     * @param Url $url The URL entity to count clicks for
     *
     * @return int The count of clicks
     */
    public function getClickCountByUrl(Url $url): int
    {
        return $this->clickRepository->countClicksByUrl($url);
    }
}
