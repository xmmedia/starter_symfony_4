<?php

declare(strict_types=1);

namespace App\Controller;

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
     * @todo-symfony if deleted, add "index" route to routes.yaml for email generation
     */
    #[Route(path: '/', name: 'index')]
    public function index(): Response
    {
        // @todo-symfony if building an admin only app
        // if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
        //     return $this->redirectToRoute('app_login');
        // }

        return $this->render('default/index.html.twig');
    }

    #[Route(path: '/pattern-library-public', name: 'pattern_library_public')]
    public function patternLibraryPublic(PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            range(1, 10), /* some random data */
            3, /* current page */
            1, /* limit per page */
        );

        return $this->render('default/pattern_library_public.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
