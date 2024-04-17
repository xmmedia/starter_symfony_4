<?php

declare(strict_types=1);

namespace App\GraphQl\Mutation\User;

use App\Controller\SecurityController;
use App\Model\User\Command\ChangePassword;
use App\Model\User\Command\VerifyUser;
use App\Security\PasswordHasher;
use App\Util\Assert;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;
use Xm\SymfonyBundle\Util\PasswordStrengthInterface;

final readonly class UserRecoverResetMutation implements MutationInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private PasswordHasher $passwordHasher,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private RequestInfoProvider $requestProvider,
        private ?PasswordStrengthInterface $passwordStrength = null,
        private ?HttpClientInterface $pwnedHttpClient = null,
    ) {
    }

    public function __invoke(#[\SensitiveParameter] Argument $args): array
    {
        $session = $this->requestProvider->currentRequest()->getSession();
        $token = $session->get(SecurityController::TOKEN_SESSION_KEY);
        $newPassword = $args['newPassword'];

        if (!$token) {
            throw new UserError('The token is invalid.', 404);
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (InvalidResetPasswordTokenException $e) {
            // 404 -> not found
            throw new UserError('The token is invalid.', 404, $e);
        } catch (ExpiredResetPasswordTokenException $e) {
            // 405 -> method not allowed
            throw new UserError('The link has expired.', 405, $e);
        }

        // done here because we need the user entity
        Assert::passwordAllowed(
            $newPassword,
            $user->email(),
            $user->firstName(),
            $user->lastName(),
            null,
            $this->passwordStrength,
            $this->pwnedHttpClient,
        );

        if (!$user->verified()) {
            $this->commandBus->dispatch(
                VerifyUser::now($user->userId()),
            );
        }

        $hashedPassword = ($this->passwordHasher)(
            $user->firstRole(),
            $newPassword
        );
        $this->commandBus->dispatch(
            ChangePassword::forUser($user->userId(), $hashedPassword),
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
