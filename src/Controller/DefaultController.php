<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Page;
use App\Projection\Page\PageFinder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(PageFinder $pageFinder): Response
    {
        // if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
        //     return $this->redirectToRoute('app_login');
        // }

        return $this->page($pageFinder->findHomepage());
    }

    public function page(Page $page): Response
    {
        $template = $page->content()['template'] ?: 'static.html.twig';

        return $this->render($template, [
            'page' => $page,
        ]);
    }

    /**
     * @Route("/pattern-library-public", name="pattern_library_public")
     */
    public function patternLibraryPublic(PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            range(1, 10), /* some random data */
            3, /* current page */
            1 /* limit per page */
        );

        return $this->render('default/pattern_library_public.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
