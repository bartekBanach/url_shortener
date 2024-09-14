<?php
/**
 * URL controller.
 *
 * @license MIT License
 */

namespace App\Controller;

use App\Dto\UrlListInputFiltersDto;
use App\Entity\Url;
use App\Form\Type\UrlType;
use App\Resolver\UrlListInputFiltersDtoResolver;
use App\Service\ClickServiceInterface;
use App\Service\TagServiceInterface;
use App\Service\UrlServiceInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
     * @param UrlServiceInterface   $urlService   Url service
     * @param TagServiceInterface   $tagService   Tag service
     * @param ClickServiceInterface $clickService Click service
     * @param TranslatorInterface   $translator   Translator
     */
    public function __construct(private readonly UrlServiceInterface $urlService, private readonly TagServiceInterface $tagService, private readonly ClickServiceInterface $clickService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param UrlListInputFiltersDto $filters Input filters
     * @param int                    $page    Page number
     *
     * @return Response HTTP response
     *
     * @throws NonUniqueResultException
     */
    #[Route(name: 'url_index', methods: 'GET')]
    public function index(#[MapQueryString(resolver: UrlListInputFiltersDtoResolver::class)] UrlListInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->urlService->getPaginatedList(
            $page,
            null,
            $filters
        );
        $tags = $this->tagService->findAll();

        return $this->render('url/index.html.twig', ['pagination' => $pagination, 'tags' => $tags, 'filters' => $filters]);
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
    #[IsGranted('VIEW', subject: 'url')]
    public function show(Url $url): Response
    {
        if ($url->getAuthor() !== $this->getUser()) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.record_not_found')
            );

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/show.html.twig',
            ['url' => $url]
        );
    }

    /**
     * Edit action.
     *
     * @param Request             $request    HTTP request
     * @param Url                 $url        URL entity
     * @param UrlServiceInterface $urlService URL service
     * @param TranslatorInterface $translator Translator
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'url_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    #[IsGranted('EDIT', subject: 'url')]
    public function edit(Request $request, Url $url, UrlServiceInterface $urlService, TranslatorInterface $translator): Response
    {
        if ($url->getAuthor() !== $this->getUser()) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.record_not_found')
            );

            return $this->redirectToRoute('url_index');
        }

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
     * @param Url     $url     URL entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'url_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'DELETE'])]
    #[IsGranted('DELETE', subject: 'url')]
    public function delete(Request $request, Url $url): Response
    {
        if ($url->getAuthor() !== $this->getUser()) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.record_not_found')
            );

            return $this->redirectToRoute('url_index');
        }

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
