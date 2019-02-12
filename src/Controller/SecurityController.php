<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // @todo-symfony
            return new RedirectResponse('/admin');
        }

        return $this->render('admin.html.twig');
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \Exception('Shouldn\'t have gotten to the login action.');
    }
}
