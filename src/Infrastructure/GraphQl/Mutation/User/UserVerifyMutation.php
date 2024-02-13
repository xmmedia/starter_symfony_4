<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Controller\SecurityController;
use App\Model\User\Command\VerifyUser;
use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;

final readonly class UserVerifyMutation implements MutationInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private Security $security,
        private RequestInfoProvider $requestProvider,
    ) {
    }

    public function __invoke(): array
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new UserError('Cannot activate account if logged in.', 404);
        }

        $session = $this->requestProvider->currentRequest()->getSession();
        $token = $session->get(SecurityController::TOKEN_SESSION_KEY);

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

        if ($user->verified()) {
            // 404 -> not found
            throw new UserError('Your account has already been activated.', 404);
        }

        $this->commandBus->dispatch(
            VerifyUser::now($user->userId()),
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
