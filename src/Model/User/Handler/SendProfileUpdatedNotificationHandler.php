<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Infrastructure\Email\EmailTemplate;
use App\Infrastructure\Service\UrlGenerator;
use App\Model\User\Command\SendProfileUpdatedNotification;
use App\Model\User\Exception\UserNotFound;
use App\Projection\User\UserFinder;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;

final readonly class SendProfileUpdatedNotificationHandler
{
    public function __construct(
        private UserFinder $userFinder,
        private UrlGenerator $urlGenerator,
        private EmailGatewayInterface $emailGateway,
    ) {
    }

    public function __invoke(SendProfileUpdatedNotification $command): void
    {
        $user = $this->userFinder->find($command->userId());
        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $this->emailGateway->send(
            EmailTemplate::USER_PROFILE_UPDATED,
            $user->email(),
            [
                'name'       => $user->name(),
                'profileUrl' => $this->urlGenerator->profile(),
            ],
        );
    }
}
