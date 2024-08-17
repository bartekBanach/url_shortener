<?php
/**
 * Url voter.
 */

namespace App\Security\Voter;

use App\Entity\Url;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UrlVoter.
 */
class UrlVoter extends Voter
{
    /**
     * Edit permission.
     *
     * @const string
     */
    private const EDIT = 'EDIT';

    /**
     * View permission.
     *
     * @const string
     */
    private const VIEW = 'VIEW';

    /**
     * Delete permission.
     *
     * @const string
     */
    private const DELETE = 'DELETE';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Url;
    }

    /**
     * Perform a single access check operation on a given attribute, subject, and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
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
        if (!$subject instanceof Url) {
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
     * Checks if the user can edit the URL.
     *
     * @param Url           $url  URL entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canEdit(Url $url, UserInterface $user): bool
    {
        return $url->getAuthor() === $user;
    }

    /**
     * Checks if the user can view the URL.
     *
     * @param Url           $url  URL entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canView(Url $url, UserInterface $user): bool
    {
        return $url->getAuthor() === $user;
    }

    /**
     * Checks if the user can delete the URL.
     *
     * @param Url           $url  URL entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canDelete(Url $url, UserInterface $user): bool
    {
        return $url->getAuthor() === $user;
    }
}
