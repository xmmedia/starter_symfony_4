<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Controller\RequestCsrfCheck;
use App\Model\User\Command\VerifyUserByAdmin;
use App\Model\User\UserId;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route(defaults={"_format": "json"})
 * @codeCoverageIgnore
 */
class UserAdminVerifyController extends AbstractController
{
    use RequestCsrfCheck;

    /**
     * @Route(
     *     "/api/admin/user/{id}/verify",
     *     name="api_admin_user_verify",
     *     methods={"POST"}
     * )
     */
    public function toggle(
        Request $request,
        MessageBusInterface $commandBus
    ): JsonResponse {
        $this->checkAdminCsrf($request);

        $userId = UserId::fromString($request->attributes->get('id'));

        $commandBus->dispatch(VerifyUserByAdmin::now($userId));

        return $this->json(['success' => true]);
    }
}
