<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Controller\RequestCsrfCheck;
use App\Exception\InvalidForm;
use App\Form\AdminUserCreateType;
use App\Form\AdminUserEditType;
use App\Model\User\Command\AdminChangePassword;
use App\Model\User\Command\AdminCreateUser;
use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\UserId;
use App\Security\TokenGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
     *     "/api/admin/user/create",
     *     name="api_admin_user_create",
     *     methods={"POST"}
     * )
     */
    public function create(
        Request $request,
        MessageBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        TokenGenerator $tokenGenerator,
        UserPasswordEncoderInterface $passwordEncoder
    ): JsonResponse {
        $this->checkAdminCsrf($request);

        $form = $formFactory
            ->create(AdminUserCreateType::class)
            ->submit($request->request->get('user'));

        if (!$form->isValid()) {
            throw InvalidForm::fromForm($form);
        }

        $userId = UserId::generate();
        if (!$form->getData()['setPassword']) {
            $password = ($tokenGenerator)();
        } else {
            $password = $form->getData()['password'];
        }

        // use the entity user because it's the one authentication is based on
        $encodedPassword = $passwordEncoder->encodePassword(
            new \App\Entity\User(),
            $password
        );

        $commandBus->dispatch(AdminCreateUser::withData(
            $userId,
            $form->getData()['email'],
            $encodedPassword,
            $form->getData()['role'],
            $form->getData()['active'],
            $form->getData()['firstName'],
            $form->getData()['lastName']
        ));

        return $this->json(['userId' => $userId->toString()]);
    }

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
        UserPasswordEncoderInterface $passwordEncoder
    ): JsonResponse {
        $this->checkAdminCsrf($request);

        $form = $formFactory
            ->create(AdminUserEditType::class)
            ->submit($request->request->get('user'));

        if (!$form->isValid()) {
            throw InvalidForm::fromForm($form);
        }

        $userId = UserId::fromString($request->attributes->get('id'));

        $commandBus->dispatch(AdminUpdateUser::withData(
            $userId,
            $form->getData()['email'],
            $form->getData()['role'],
            $form->getData()['firstName'],
            $form->getData()['lastName']
        ));

        if ($form->getData()['changePassword']) {
            $encodedPassword = $passwordEncoder->encodePassword(
                new \App\Entity\User(),
                $form->getData()['password']
            );

            $commandBus->dispatch(
                AdminChangePassword::withData($userId, $encodedPassword)
            );
        }

        return $this->json(['success' => true]);
    }
}
