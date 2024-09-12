<?php
/**
 * User voter.
 */

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserVoter.
 */
class UserVoter extends Voter
{
    private const EDIT = 'EDIT';
    private const VIEW = 'VIEW';
    private const DELETE = 'DELETE';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof User;
    }

    /**
     * Perform a single access check operation on a given attribute, subject, and token.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if the user can edit the user entity.
     *
     * @param User          $subject User entity to be edited
     * @param UserInterface $user    Logged-in user
     *
     * @return bool Result
     */
    private function canEdit(User $subject, UserInterface $user): bool
    {
        return $subject === $user || in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Checks if the user can view the user entity.
     *
     * @param User          $subject User entity to be viewed
     * @param UserInterface $user    Logged-in user
     *
     * @return bool Result
     */
    private function canView(User $subject, UserInterface $user): bool
    {
        return $subject === $user || in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Checks if the user can delete the user entity.
     *
     * @param User          $subject User entity to be deleted
     * @param UserInterface $user    Logged-in user
     *
     * @return bool Result
     */
    private function canDelete(User $subject, UserInterface $user): bool
    {
        return $subject !== $user && in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}
