<?php

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UserServiceInterface.
 */
interface UserServiceInterface
{
    /**
     * Get paginated list of users.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, User> Paginated list of users
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save a user entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void;

    /**
     * Delete a user entity.
     *
     * @param User $user User entity
     */
    public function delete(User $user): void;

    /**
     * Find one user by ID.
     *
     * @param int $id User ID
     *
     * @return User|null User entity or null if not found
     */
    public function findOneById(int $id): ?User;
}
