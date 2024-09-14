<?php
/**
 * EmailVerifier class.
 *
 * Handles email verification processes, such as sending and confirming verification emails.
 */

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Class EmailVerifier.
 */
class EmailVerifier
{
    /**
     * Constructor.
     *
     * @param VerifyEmailHelperInterface $verifyEmailHelper Email helper for generating and validating verification URLs
     * @param MailerInterface            $mailer            Mailer service
     * @param EntityManagerInterface     $entityManager     Entity manager for persisting user verification
     */
    public function __construct(private readonly VerifyEmailHelperInterface $verifyEmailHelper, private readonly MailerInterface $mailer, private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Sends email confirmation for the user.
     *
     * @param string         $verifyEmailRouteName Route name used to generate the verification URL
     * @param User           $user                 The user entity for whom the email is sent
     * @param TemplatedEmail $email                The email template being sent
     */
    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, TemplatedEmail $email): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            (string) $user->getId(),
            $user->getEmail()
        );

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * Handles email confirmation process from the request.
     *
     * @param Request $request The HTTP request containing the confirmation details
     * @param User    $user    The user entity being verified
     */
    public function handleEmailConfirmation(Request $request, User $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest($request, (string) $user->getId(), $user->getEmail());

        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
