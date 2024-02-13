<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Service\DefaultRouteProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @codeCoverageIgnore
 */
final class DefaultController extends AbstractController
{
    #[Route(path: '/', name: 'index')]
    public function index(DefaultRouteProvider $defaultRoute): Response
    {
        // @todo-symfony if building an admin only app
        // if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
        //     return $this->redirectToRoute(...$defaultRoute());
        // }

        return $this->render('default/index.html.twig');
    }
}
