<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\RequestCsrfCheck;
use App\Exception\FormValidationException;
use App\Form\UserVerifyType;
use App\Model\User\Command\ChangePassword;
use App\Model\User\Command\VerifyUser;
use App\Model\User\Exception\InvalidToken;
use App\Model\User\Exception\TokenHasExpired;
use App\Security\AppAuthenticator;
use App\Security\PasswordEncoder;
use App\Security\TokenValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

/**
 * @Route(defaults={"_format": "json"})
 * @codeCoverageIgnore
 */
class UserVerifyController extends AbstractController
{
    use RequestCsrfCheck;

    /**
     * @Route(
     *     "/api/user/activate",
     *     name="api_user_activate",
     *     methods={"POST"}
     * )
     */
    public function activate(
        Request $request,
        MessageBusInterface $commandBus,
        TokenValidator $tokenValidator,
        FormFactoryInterface $formFactory,
        PasswordEncoder $passwordEncoder,
        GuardAuthenticatorHandler $authHandler,
        AppAuthenticator $appAuthenticator
    ): JsonResponse {
        $this->checkAdminCsrf($request);

        $form = $formFactory
            ->create(UserVerifyType::class)
            ->submit($request->request->get('data'));

        if (!$form->isValid()) {
            throw FormValidationException::fromForm($form);
        }

        try {
            $user = $tokenValidator->validate($form->getData()['token']);
        } catch (InvalidToken $e) {
            throw $this->createNotFoundException('The token is invalid.', $e);
        } catch (TokenHasExpired $e) {
            return $this->json(
                ['error' => 'The link has expired.'],
                Response::HTTP_METHOD_NOT_ALLOWED
            );
        }

        if ($user->verified()) {
            throw $this->createNotFoundException(
                'Your account has already been activated.'
            );
        }

        $commandBus->dispatch(
            VerifyUser::now($user->id())
        );

        $encodedPassword = ($passwordEncoder)(
            new Role($user->roles()[0]),
            $form->getData()['password']
        );
        $commandBus->dispatch(
            ChangePassword::forUser($user->id(), $encodedPassword)
        );

        $authHandler->authenticateUserAndHandleSuccess(
            $user,
            $request,
            $appAuthenticator,
            'main'
        );

        return $this->json(['success' => true]);
    }
}
