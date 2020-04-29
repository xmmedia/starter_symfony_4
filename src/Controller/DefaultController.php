<?php

declare(strict_types=1);

namespace App\Controller;

use App\Document\Page;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class DefaultController extends AbstractController
{
    /** @var string */
    private $defaultTemplate;

    /** @var string */
    private $contentBasePath;

    public function __construct(
        string $defaultTemplate,
        string $contentBasePath
    ) {
        $this->defaultTemplate = $defaultTemplate;
        $this->contentBasePath = $contentBasePath;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(DocumentManagerInterface $dm): Response
    {
        // @todo-symfony use if only an admin site
        // if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
        //     return $this->redirectToRoute('app_login');
        // }

        $homePage = $dm->find(
            Page::class,
            sprintf('%s/home', $this->contentBasePath)
        );
        if (!$homePage) {
            throw $this->createNotFoundException('No homepage configured');
        }

        return $this->page($homePage);
    }

    /**
     * The main action that shows the pages.
     */
    public function page(Page $contentDocument): Response
    {
        $template = $contentDocument->template() ?: $this->defaultTemplate;

        return $this->render($template, [
            'content' => $contentDocument,
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
