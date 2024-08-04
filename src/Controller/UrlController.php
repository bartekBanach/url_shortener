<?php
/**
 * URL controller.
 */

namespace App\Controller;

use App\Entity\Url;
use App\Form\Type\UrlType;
use App\Repository\UrlRepository;
use App\Service\UrlServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UrlController.
 */
#[Route('/url')]
class UrlController extends AbstractController
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
     * Index action.
     *
     * @param Request            $request       HTTP Request
     * @param UrlRepository      $urlRepository URL repository
     * @param PaginatorInterface $paginator     Paginator
     *
     * @return Response HTTP response
     */
    #[Route(name: 'url_index', methods: 'GET')]
    public function index(Request $request, UrlRepository $urlRepository, PaginatorInterface $paginator, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->urlService->getPaginatedList($page);

        return $this->render('url/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Url $url URL entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'url_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(Url $url): Response
    {
        return $this->render(
            'url/show.html.twig',
            ['url' => $url]
        );
    }

    /**
     * Create action.
     *
     * @param Request             $request    HTTP request
     * @param UrlServiceInterface $urlService URL service
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'url_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request, UrlServiceInterface $urlService): Response
    {
        $url = new Url();
        $form = $this->createForm(UrlType::class, $url);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $urlService->save($url);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Url     $url     URL entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'url_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, Url $url, UrlServiceInterface $urlService, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(
            UrlType::class,
            $url,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('url_edit', ['id' => $url->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $urlService->save($url);

            $this->addFlash(
                'success',
                $translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/edit.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }


    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Url     $url     Url entity
     *
     * @return Response HTTP response
     */
    #[Route('/url/{id}/delete', name: 'url_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'DELETE'])]
    public function delete(Request $request, Url $url): Response
    {
        $form = $this->createForm(FormType::class, $url, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('url_delete', ['id' => $url->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->urlService->delete($url);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/delete.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }



}
