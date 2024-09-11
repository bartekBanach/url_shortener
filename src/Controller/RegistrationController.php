<?php
/**
 * Controller handling user registration and email verification.
 *
 */

namespace App\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

/**
 * Class RegistrationController.
 *
 * Handles user registration, email confirmation, and email verification.
 */
class RegistrationController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param EmailVerifier       $emailVerifier Email verifier service
     * @param TranslatorInterface $translator    Translator service
     */
    public function __construct(private EmailVerifier $emailVerifier, private TranslatorInterface $translator)
    {
    }

    /**
     * Registration action.
     *
     * @param Request                     $request            HTTP request
     * @param UserPasswordHasherInterface $userPasswordHasher Password hasher service
     * @param Security                    $security           Security service
     * @param EntityManagerInterface      $entityManager      Entity manager service
     *
     * @return Response HTTP response
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setRoles([UserRole::ROLE_USER->value]);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // Generate a signed URL and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('admin@url-shortener.com', 'Url Shortener Support'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // Do anything else you need here, like send an email

            $this->addFlash(
                'success',
                $this->translator->trans('message.registered_successfully')
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /**
     * Email verification action.
     *
     * @param Request             $request    HTTP request
     * @param TranslatorInterface $translator Translator service
     *
     * @return Response HTTP response
     */
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Validate email confirmation link, set User::isVerified=true and persist
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
