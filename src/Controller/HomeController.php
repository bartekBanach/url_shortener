<?php

namespace App\Controller;

use App\Entity\Url;
use App\Form\Type\UrlType;
use App\Service\UrlServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Home controller.
 */
class HomeController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param UrlServiceInterface $urlService Url service
     * @param TranslatorInterface $translator Translator
     */
    public function __construct(private readonly UrlServiceInterface $urlService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Redirects a shortened URL to its original long URL.
     *
     * This action captures the shortened URL code from the URL and looks up
     * the corresponding long URL in the repository. If found, it redirects the
     * user to the long URL. If not found, it throws a 404 error.
     *
     * @param string $code The shortened URL code
     *
     * @return Response HTTP response
     */
    #[Route('/{code}', name: 'redirect', requirements: ['code' => '.+'], methods: ['GET'], priority: -1)]
    public function redirectToLongUrl(string $code): Response
    {
        $url = $this->urlService->findUrlByShortUrl($code);

        if (!$url) {
            throw $this->createNotFoundException('URL not found.');
        }

        return $this->redirect($url->getLongUrl());
    }

    /**
     * Displays the main page with a URL form.
     *
     * @param Request            $request             HTTP request
     * @param RateLimiterFactory $anonymousApiLimiter Rate limiter
     *
     * @return Response HTTP response
     */
    #[Route('/', name: 'home', methods: ['GET', 'POST'])]
    public function index(Request $request, RateLimiterFactory $anonymousApiLimiter): Response
    {
        $limiter = $anonymousApiLimiter->create($request->getClientIp());
        $isAuthenticated = null !== $this->getUser();
        $user = $this->getUser();

        $url = new Url();
        $url->setAuthor($user);
        $form = $this->createForm(UrlType::class, $url, [
            'csrf_protection' => false, // for testing
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$user && false === $limiter->consume(1)->isAccepted()) {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('message.rate_limit_exceeded')
                );

                return $this->redirectToRoute('home');
            }

            $this->urlService->save($url);
            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );
            $this->redirectToRoute('home');

            $url = new Url();
            $form = $this->createForm(UrlType::class, $url, [
                'csrf_protection' => false, // for testing
            ]);
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
