<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\ChangePassword;
use App\Model\User\Command\VerifyUser;
use App\Model\User\Exception\InvalidToken;
use App\Model\User\Exception\TokenHasExpired;
use App\Model\User\Token;
use App\Security\PasswordHasher;
use App\Security\Security;
use App\Security\TokenValidator;
use App\Util\Assert;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Xm\SymfonyBundle\Util\PasswordStrengthInterface;

class UserRecoverResetMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var PasswordHasher */
    private $passwordHasher;

    /** @var TokenValidator */
    private $tokenValidator;

    /** @var Security */
    private $security;

    /** @var PasswordStrengthInterface|null */
    private $passwordStrength;

    /** @var HttpClientInterface|null */
    private $pwnedHttpClient;

    public function __construct(
        MessageBusInterface $commandBus,
        PasswordHasher $passwordHasher,
        TokenValidator $tokenValidator,
        Security $security,
        PasswordStrengthInterface $passwordStrength = null,
        HttpClientInterface $pwnedHttpClient = null
    ) {
        $this->commandBus = $commandBus;
        $this->passwordHasher = $passwordHasher;
        $this->tokenValidator = $tokenValidator;
        $this->security = $security;
        $this->passwordStrength = $passwordStrength;
        $this->pwnedHttpClient = $pwnedHttpClient;
    }

    public function __invoke(Argument $args): array
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new UserError('Logged in users cannot change their password this way.', 404);
        }

        $newPassword = $args['newPassword'];

        try {
            $user = $this->tokenValidator->validate(
                Token::fromString($args['token'])
            );
        } catch (InvalidToken $e) {
            // 404 -> not found
            throw new UserError('The token is invalid.', 404, $e);
        } catch (TokenHasExpired $e) {
            // 405 -> method not allowed
            throw new UserError('The link has expired.', 405);
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
                VerifyUser::now($user->userId())
            );
        }

        $encodedPassword = ($this->passwordHasher)(
            $user->firstRole(),
            $newPassword
        );
        $this->commandBus->dispatch(
            ChangePassword::forUser($user->userId(), $encodedPassword)
        );

        // we would log the user in right away, but as we don't have a request
        // and the projection might not be caught up, we don't try

        return [
            'success' => true,
        ];
    }
}
