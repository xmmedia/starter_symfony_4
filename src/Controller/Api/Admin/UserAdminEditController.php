<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Controller\RequestCsrfCheck;
use App\Exception\FormValidationException;
use App\Form\AdminUserEditType;
use App\Model\User\Command\AdminChangePassword;
use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\UserId;
use App\Security\PasswordEncoder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route(defaults={"_format": "json"})
 * @codeCoverageIgnore
 */
class UserAdminEditController extends AbstractController
{
    use RequestCsrfCheck;

    /**
     * @Route(
     *     "/api/admin/user/{id}",
     *     name="api_admin_user_update",
     *     methods={"POST"}
     * )
     */
    public function update(
        Request $request,
        MessageBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        PasswordEncoder $passwordEncoder
    ): JsonResponse {
        $this->checkAdminCsrf($request);

        $form = $formFactory
            ->create(AdminUserEditType::class)
            ->submit($request->request->get('user'));

        if (!$form->isValid()) {
            throw FormValidationException::fromForm($form);
        }

        $userId = UserId::fromString($request->attributes->get('id'));

        $commandBus->dispatch(AdminUpdateUser::with(
            $userId,
            $form->getData()['email'],
            $form->getData()['role'],
            $form->getData()['firstName'],
            $form->getData()['lastName']
        ));

        if ($form->getData()['changePassword']) {
            $encodedPassword = ($passwordEncoder)(
                $form->getData()['role'],
                $form->getData()['password']
            );

            $commandBus->dispatch(
                AdminChangePassword::with($userId, $encodedPassword)
            );
        }

        return $this->json(['success' => true]);
    }
}
