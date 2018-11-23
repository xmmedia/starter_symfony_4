<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\RequestCsrfCheck;
use App\Exception\InvalidForm;
use App\Form\UserChangePasswordType;
use App\Form\UserProfileType;
use App\Model\User\Command\ChangeUserPassword;
use App\Model\User\Command\UpdateUserProfile;
use App\Security\PasswordEncoder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
 * @Route(defaults={"_format": "json"})
 * @codeCoverageIgnore
 */
class UserProfileEditController extends AbstractController
{
    use RequestCsrfCheck;

    /**
     * @Route(
     *     "/api/user/profile",
     *     name="api_user_profile",
     *     methods={"POST"}
     * )
     *
     * @param UserInterface|\App\Entity\User $user
     */
    public function save(
        Request $request,
        MessageBusInterface $commandBus,
        UserInterface $user = null,
        FormFactoryInterface $formFactory
    ): JsonResponse {
        $this->checkAdminCsrf($request);

        // @todo check what else FOS User does

        $form = $formFactory
            ->create(UserProfileType::class)
            ->submit($request->request->get('user'));

        if (!$form->isValid()) {
            throw InvalidForm::fromForm($form);
        }

        $commandBus->dispatch(
            UpdateUserProfile::withData(
                $user->id(),
                $form->getData()['email'],
                $form->getData()['firstName'],
                $form->getData()['lastName']
            )
        );

        return $this->json(['success' => true]);
    }

    /**
     * @Route(
     *     "/api/user/profile/password",
     *     name="api_user_profile_password",
     *     methods={"POST"}
     * )
     *
     * @param UserInterface|\App\Entity\User $user
     */
    public function changePassword(
        Request $request,
        MessageBusInterface $commandBus,
        UserInterface $user = null,
        FormFactoryInterface $formFactory,
        PasswordEncoder $passwordEncoder
    ): JsonResponse {
        $this->checkAdminCsrf($request);

        // @todo check what else FOS User does

        $form = $formFactory
            ->create(UserChangePasswordType::class)
            ->submit($request->request->get('user'));

        if (!$form->isValid()) {
            throw InvalidForm::fromForm($form);
        }

        $encodedPassword = ($passwordEncoder)(
            new Role($user->getRoles()[0]),
            $form->getData()['newPassword']
        );

        $commandBus->dispatch(
            ChangeUserPassword::forUser(
                $user->id(),
                $encodedPassword
            )
        );

        return $this->json(['success' => true]);
    }
}
