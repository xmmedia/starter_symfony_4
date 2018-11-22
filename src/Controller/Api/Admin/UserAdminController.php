<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin;

use App\Controller\RequestCsrfCheck;
use App\Exception\InvalidForm;
use App\Form\AdminUserCreateType;
use App\Model\User\Command\AdminCreateUser;
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
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @IsGranted("ROLE_ADMIN")
 * @todo test if this & below are both needed
 * @Route(defaults={"_format": "json"})
 * @codeCoverageIgnore
 */
class UserAdminController extends AbstractController
{
    use RequestCsrfCheck;

    /**
     * @Route(
     *     "/api/admin/user/create",
     *     name="api_admin_user_create",
     *     methods={"POST"},
     *     defaults={"_format": "json"}
     * )
     *
     * @param UserInterface|\App\Entity\User $user
     */
    public function save(
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
            $password = $tokenGenerator->generateToken();
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
            $form->getData()['enabled'],
            $form->getData()['firstName'],
            $form->getData()['firstName']
        ));

        return $this->json(['userId' => $userId->toString()]);
    }
}
