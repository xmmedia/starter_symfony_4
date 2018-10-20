<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $request): Response
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/pattern-library-public", name="pattern_library_public")
     */
    public function patternLibraryPublicAction(PaginatorInterface $paginator): Response
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

    /**
     * @Route("/pattern-library-admin", name="pattern_library_admin")
     */
    public function patternLibraryAdminAction(PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            range(1, 10), /* some random data */
            3, /* current page */
            1 /* limit per page */
        );

        return $this->render('default/pattern_library_admin.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
