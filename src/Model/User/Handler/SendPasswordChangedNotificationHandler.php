<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Infrastructure\Service\UrlGenerator;
use App\Model\User\Command\SendPasswordChangedNotification;
use App\Model\User\Exception\UserNotFound;
use App\Projection\User\UserFinder;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;

final readonly class SendPasswordChangedNotificationHandler
{
    public function __construct(
        private UserFinder $userFinder,
        private UrlGenerator $urlGenerator,
        private EmailGatewayInterface $emailGateway,
        private string $template,
    ) {
    }

    public function __invoke(SendPasswordChangedNotification $command): void
    {
        $user = $this->userFinder->find($command->userId());
        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $this->emailGateway->send(
            $this->template,
            $user->email(),
            [
                'name'       => $user->name(),
                'profileUrl' => $this->urlGenerator->profile(),
            ],
        );
    }
}
