<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\SendActivation;
use App\Model\User\Exception\UserAlreadyVerified;
use App\Model\User\Exception\UserNotActive;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;
use App\Projection\User\UserFinder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\Email;

final readonly class SendActivationHandler
{
    public function __construct(
        private UserList $userRepo,
        private UserFinder $userFinder,
        private EmailGatewayInterface $emailGateway,
        private string $template,
        private string $emailFrom,
        private RouterInterface $router,
        private ResetPasswordHelperInterface $resetPasswordHelper,
    ) {
    }

    public function __invoke(SendActivation $command): void
    {
        $userAr = $this->userRepo->get($command->userId());
        if (!$userAr) {
            throw UserNotFound::withUserId($command->userId());
        }

        if ($userAr->verified()) {
            throw UserAlreadyVerified::triedToSendVerification($command->userId());
        }

        if (!$userAr->active()) {
            throw UserNotActive::triedToSendVerification($command->userId());
        }

        $user = $this->userFinder->find($command->userId());
        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        // 60 seconds * 60 minutes * 24 hours * 14 days = 1,209,600 seconds
        $token = $this->resetPasswordHelper->generateResetToken($user, 60 * 60 * 24 * 14);

        $verifyUrl = $this->router->generate(
            'user_activate_token',
            ['token' => $token->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $messageId = $this->emailGateway->send(
            $this->template,
            $user->email(),
            [
                'verifyUrl' => $verifyUrl,
                'name'      => $user->name(),
                'email'     => $command->email()->toString(),
            ],
            null,
            null,
            null,
            // add References header to prevent message threading in Gmail
            ['References' => $this->emailGateway->getReferencesEmail(Email::fromString($this->emailFrom))],
        );

        $userAr->inviteSent($messageId);

        $this->userRepo->save($userAr);
    }
}
