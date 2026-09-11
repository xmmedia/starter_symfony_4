<?php

declare(strict_types=1);

namespace App\GraphQl\Mutation\User;

use App\Controller\SecurityController;
use App\Infrastructure\Service\UserPasswordStore;
use App\Model\User\Command\ActivateUser;
use App\Model\User\Command\ChangePassword;
use App\Security\PasswordHasher;
use App\Security\Security;
use App\Util\Assert;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Error\LinkExpiredError;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Error\NotFoundError;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;
use Xm\SymfonyBundle\Util\PasswordStrengthInterface;

final readonly class UserActivateMutation implements MutationInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private PasswordHasher $passwordHasher,
        private UserPasswordStore $passwordStore,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private Security $security,
        private RequestInfoProvider $requestProvider,
        private ?PasswordStrengthInterface $passwordStrength = null,
        private ?HttpClientInterface $pwnedHttpClient = null,
    ) {
    }

    public function __invoke(#[\SensitiveParameter] Argument $args): array
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new NotFoundError('Cannot activate account if logged in.');
        }

        $session = $this->requestProvider->currentRequest()->getSession();
        $token = $session->get(SecurityController::TOKEN_SESSION_KEY);
        $password = $args['password'];

        if (!$token) {
            throw new NotFoundError('The token is invalid.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (InvalidResetPasswordTokenException $e) {
            throw new NotFoundError('The token is invalid.', $e);
        } catch (ExpiredResetPasswordTokenException $e) {
            throw new LinkExpiredError('The link has expired.', $e);
        }

        // done here because we need the user entity
        Assert::passwordAllowed(
            $password,
            $user->email(),
            $user->firstName(),
            $user->lastName(),
            null,
            $this->passwordStrength,
            $this->pwnedHttpClient,
        );

        if ($user->verified()) {
            throw new NotFoundError('Your account has already been activated.');
        }

        $this->commandBus->dispatch(
            ActivateUser::now($user->userId()),
        );

        // the hash is deliberately not part of the command
        $this->passwordStore->store(
            $user->userId(),
            ($this->passwordHasher)($user->firstRole(), $password),
        );

        $this->commandBus->dispatch(
            ChangePassword::now($user->userId()),
        );

        $this->resetPasswordHelper->removeResetRequest($token);
        $session->remove(SecurityController::TOKEN_SESSION_KEY);

        // we would log the user in right away, but as we don't have a request
        // and the projection might not be caught up, we don't try

        return [
            'success' => true,
        ];
    }
}
