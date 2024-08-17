<?php

/**
 * Tag service interface.
 */

namespace App\Service;

use App\Entity\Tag;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface TagServiceInterface.
 */
interface TagServiceInterface
{
    /**
     * Get paginated list of tags.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, Tag> Paginated list of tags
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save tag entity.
     *
     * @param Tag $tag Tag entity
     */
    public function save(Tag $tag): void;

    /**
     * Delete tag entity.
     *
     * @param Tag $tag Tag entity
     */
    public function delete(Tag $tag): void;

    /**
     * Find by title.
     *
     * @param string $title Tag title
     *
     * @return Tag|null Tag entity
     */
    public function findOneByTitle(string $title): ?Tag;

    /**
     * Find by id.
     *
     * @param int $id Tag id
     *
     * @return Tag|null Tag entity
     *
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?Tag;


    /**
     * Find all tags.
     *
     * @return Tag[] List of tags
     */
    public function findAll(): array;

}
