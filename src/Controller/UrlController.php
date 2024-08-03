<?php
/**
 * URL controller.
 */

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class UrlController.
 */
#[Route('/url')]
class UrlController extends AbstractController
{
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
        $pagination = $paginator->paginate(
            $urlRepository->queryAll(),
            $page,
            UrlRepository::PAGINATOR_ITEMS_PER_PAGE
        );

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
}
