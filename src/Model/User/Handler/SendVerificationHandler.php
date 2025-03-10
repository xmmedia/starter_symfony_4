<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\SendVerification;
use App\Model\User\Exception\UserAlreadyVerified;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;
use App\Projection\User\UserFinder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\StringUtil;

final readonly class SendVerificationHandler
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

    public function __invoke(SendVerification $command): void
    {
        $userAr = $this->userRepo->get($command->userId());
        if (!$userAr) {
            throw UserNotFound::withUserId($command->userId());
        }

        if ($userAr->verified()) {
            throw UserAlreadyVerified::triedToSendVerification($command->userId());
        }

        $user = $this->userFinder->find($command->userId());
        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $name = StringUtil::trim(\sprintf(
            '%s %s',
            $command->firstName(),
            $command->lastName(),
        ));
        $token = $this->resetPasswordHelper->generateResetToken($user);

        $verifyUrl = $this->router->generate(
            'user_verify_token',
            ['token' => $token->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $messageId = $this->emailGateway->send(
            $this->template,
            Email::fromString($command->email()->toString(), $name),
            [
                'verifyUrl' => $verifyUrl,
                'name'      => $name,
                'email'     => $command->email()->toString(),
            ],
            null,
            null,
            null,
            // add References header to prevent message threading in Gmail
            ['References' => $this->emailGateway->getReferencesEmail(Email::fromString($this->emailFrom))],
        );

        $userAr->verificationSent($messageId);

        $this->userRepo->save($userAr);
    }
}
