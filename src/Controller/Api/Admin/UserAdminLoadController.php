<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Controller\RequestCsrfCheck;
use App\Entity\User;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route(defaults={"_format": "json"})
 * @codeCoverageIgnore
 */
class UserAdminLoadController extends AbstractController
{
    use RequestCsrfCheck;

    /**
     * @Route(
     *     "/api/admin/users",
     *     name="api_admin_users",
     *     methods={"GET"}
     * )
     */
    public function list(
        Request $request,
        UserRepository $userRepo
    ): JsonResponse {
        $this->checkAdminCsrf($request);

        $users = $userRepo->findBy([], ['email' => 'ASC']);

        return $this->json(
            ['users' => $users],
            Response::HTTP_OK,
            [],
            ['groups' => ['user_admin']]
        );
    }

    /**
     * @Route(
     *     "/api/admin/user/{id}",
     *     name="api_admin_user",
     *     methods={"GET"}
     * )
     */
    public function load(Request $request, User $user): JsonResponse
    {
        $this->checkAdminCsrf($request);

        return $this->json(
            ['user' => $user],
            Response::HTTP_OK,
            [],
            ['groups' => ['user_admin']]
        );
    }
}
