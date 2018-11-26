<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecoverController extends AbstractController
{
    /**
     * @Route("/recover/initiate", name="1")
     */
    public function initiate(): Response
    {
        return $this->render('recover/initiate.html.twig', [
        ]);
    }

    /**
     * @Route("/recover/reset", name="2")
     */
    public function reset(): Response
    {
        return $this->render('recover/reset.html.twig', [
        ]);
    }

    /**
     * @Route("/activate/{token}", name="user_verify")
     */
    public function verify(): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('recover/verify.html.twig');
    }
}
