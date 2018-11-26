<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class UserRecoverController extends AbstractController
{
    /**
     * @Route(
     *     "/recover/{action}/{token?}",
     *     name="forgot_password",
     *     methods={"GET"},
     *     defaults={"action": "initiate"},
     *     requirements={"action": "initiate|reset"}
     * )
     */
    public function reset(): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('recover/reset.html.twig');
    }

    /**
     * @Route("/activate/{token}", name="user_verify", methods={"GET"})
     */
    public function verify(): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('recover/verify.html.twig');
    }
}
