<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Controller\RequestCsrfCheck;
use App\Model\User\Command\ActivateUserByAdmin;
use App\Model\User\Command\DeactivateUserByAdmin;
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
class UserAdminActivateController extends AbstractController
{
    use RequestCsrfCheck;

    /**
     * @Route(
     *     "/api/admin/user/{id}/{action}",
     *     name="api_admin_user_activate",
     *     methods={"POST"},
     *     requirements={"action": "activate|deactivate"}
     * )
     */
    public function toggle(
        Request $request,
        MessageBusInterface $commandBus
    ): JsonResponse {
        $this->checkAdminCsrf($request);

        $userId = UserId::fromString($request->attributes->get('id'));
        $action = strtolower($request->attributes->get('action'));

        if ($action == 'activate') {
            $command = ActivateUserByAdmin::class;
        } else {
            $command = DeactivateUserByAdmin::class;
        }

        $commandBus->dispatch($command::user($userId));

        return $this->json(['success' => true]);
    }
}
