<?php

declare(strict_types=1);

namespace App\GraphQl\Mutation\User;

use App\Controller\SecurityController;
use App\Model\User\Command\VerifyUser;
use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Error\LinkExpiredError;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Error\NotFoundError;
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
            throw new NotFoundError('Cannot activate account if logged in.');
        }

        $session = $this->requestProvider->currentRequest()->getSession();
        $token = $session->get(SecurityController::TOKEN_SESSION_KEY);

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

        if ($user->verified()) {
            throw new NotFoundError('Your account has already been activated.');
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
