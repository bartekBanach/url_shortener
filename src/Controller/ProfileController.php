<?php
/**
 * Profile controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\ProfileType;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ProfileController.
 */
#[Route('/profile')]
class ProfileController extends AbstractController
{
    private TranslatorInterface $translator;
    private UserServiceInterface $userService;

    /**
     * Constructor.
     *
     * @param TranslatorInterface  $translator  Translator
     * @param UserServiceInterface $userService User service
     */
    public function __construct(TranslatorInterface $translator, UserServiceInterface $userService)
    {
        $this->translator = $translator;
        $this->userService = $userService;
    }

    /**
     * View profile action.
     *
     * @return Response HTTP response
     */
    #[Route('/', name: 'profile_index')]
    public function viewProfile(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException($this->translator->trans('error.access_denied'));
        }

        return $this->render('profile/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Edit profile action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'profile_edit')]
    #[IsGranted('EDIT', subject: 'user')]
    public function editProfile(Request $request, User $user): Response
    {
        if ($user !== $this->getUser()) {
            throw $this->createAccessDeniedException($this->translator->trans('error.access_denied'));
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->save($user);
            $this->addFlash('success', $this->translator->trans('message.updated_successfully'));

            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
