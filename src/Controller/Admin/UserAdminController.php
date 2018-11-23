<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN")
 * @codeCoverageIgnore
 */
class UserAdminController extends AbstractController
{
    /**
     * @Route(
     *     "/admin/user/{action?}",
     *     name="admin_user",
     *     methods={"GET"}
     * )
     * @Route(
     *     "/admin/user/{userId}/{action}",
     *     methods={"GET"}
     * )
     */
    public function index(): Response
    {
        return $this->render('user_admin/index.html.twig');
    }
}
