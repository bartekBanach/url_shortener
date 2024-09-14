<?php
/**
 * URL controller.
 */

namespace App\Controller\Admin;

use App\Dto\UrlListInputFiltersDto;
use App\Entity\Url;
use App\Form\Type\UrlType;
use App\Resolver\UrlListInputFiltersDtoResolver;
use App\Service\ClickServiceInterface;
use App\Service\TagServiceInterface;
use App\Service\UrlServiceInterface;
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
#[Route('/admin/url')]
#[IsGranted('ROLE_ADMIN')]
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
     */
    #[Route(name: 'admin_url_index', methods: 'GET')]
    public function index(#[MapQueryString(resolver: UrlListInputFiltersDtoResolver::class)] UrlListInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->urlService->getPaginatedList(
            $page,
            null,
            $filters
        );
        $tags = $this->tagService->findAll();

        return $this->render('admin/url/index.html.twig', ['pagination' => $pagination, 'tags' => $tags, 'filters' => $filters]);
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
        name: 'admin_url_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(Url $url): Response
    {

        return $this->render(
            'admin/url/show.html.twig',
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
        name: 'admin_url_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request, UrlServiceInterface $urlService): Response
    {
        $user = $this->getUser();

        $url = new Url();
        $url->setAuthor($user);
        $form = $this->createForm(UrlType::class, $url);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $urlService->save($url);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('admin_url_index');
        }

        return $this->render(
            'admin/url/create.html.twig',
            ['form' => $form->createView()]
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
    #[Route('/{id}/edit', name: 'admin_url_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, Url $url, UrlServiceInterface $urlService, TranslatorInterface $translator): Response
    {

        $form = $this->createForm(
            UrlType::class,
            $url,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('admin_url_edit', ['id' => $url->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $urlService->save($url);

            $this->addFlash(
                'success',
                $translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('admin_url_index');
        }

        return $this->render(
            'admin/url/edit.html.twig',
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
    #[Route('/{id}/delete', name: 'admin_url_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'DELETE'])]
    public function delete(Request $request, Url $url): Response
    {


        $form = $this->createForm(FormType::class, $url, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('admin_url_delete', ['id' => $url->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->urlService->delete($url);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('admin_url_index');
        }

        return $this->render(
            'admin/url/delete.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }
}
