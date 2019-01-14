<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class ContactController extends AbstractController
{
    /**
     * @Route("/contact-us", name="contact", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }
}
