<?php
/**
 * Click service interface.
 */

namespace App\Service;

use App\Entity\Click;
use App\Entity\Url;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Interface ClickServiceInterface.
 *
 * Service interface for managing Click entities.
 */
interface ClickServiceInterface
{
    /**
     * Save a click entity.
     *
     * @param Click $click Click entity to be saved
     */
    public function save(Click $click): void;

    /**
     * Get clicks by URL.
     *
     * Retrieves all clicks associated with a specific URL.
     *
     * @param Url $url The URL entity to retrieve clicks for
     *
     * @return Click[] List of Click entities associated with the URL
     */
    public function getClicksByUrl(Url $url): array;

    /**
     * Get the count of clicks by URL.
     *
     * Retrieves the total number of clicks associated with a specific URL.
     *
     * @param Url $url The URL entity to count clicks for
     *
     * @return int The count of clicks
     *
     * @throws NonUniqueResultException
     */
    public function getClickCountByUrl(Url $url): int;
}
