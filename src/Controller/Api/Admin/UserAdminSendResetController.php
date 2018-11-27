<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Controller\RequestCsrfCheck;
use App\Model\User\Command\InitiatePasswordRecovery;
use App\Model\User\UserId;
use App\Repository\UserRepository;
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
class UserAdminSendResetController extends AbstractController
{
    use RequestCsrfCheck;

    /**
     * @Route(
     *     "/api/admin/user/{id}/send-reset",
     *     name="api_admin_user_send_reset",
     *     methods={"POST"}
     * )
     */
    public function sendReset(
        Request $request,
        MessageBusInterface $commandBus,
        UserRepository $userRepo
    ): JsonResponse {
        $this->checkAdminCsrf($request);

        $userId = UserId::fromString($request->attributes->get('id'));

        $user = $userRepo->find($userId);
        if (!$user) {
            throw $this->createNotFoundException('The user could not be found.');
        }

        $commandBus->dispatch(
            InitiatePasswordRecovery::now($user->id(), $user->email())
        );

        return $this->json(['success' => true]);
    }
}
