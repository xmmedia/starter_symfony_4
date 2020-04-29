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

    public function __construct(string $defaultTemplate)
    {
        $this->defaultTemplate = $defaultTemplate;
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

        $contentBasePath = $this->getParameter('cmf_content.persistence.phpcr.content_basepath');
        $homePage = $dm->find(Page::class, $contentBasePath);
        if (!$homePage) {
            throw $this->createNotFoundException('No homepage configured');
        }

        return $this->page($homePage);
    }

    /**
     * The main action that shows the pages.
     *
     * @param object $contentDocument
     */
    public function page($contentDocument): Response
    {
        $template = $contentDocument->template();
        $template = $template ?: $this->defaultTemplate;

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
