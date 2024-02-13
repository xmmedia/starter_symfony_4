<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\SendLoginLink;
use App\Model\User\Exception\UserNotFound;
use App\Projection\User\UserFinder;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\Email;

final readonly class SendLoginLinkHandler
{
    public function __construct(
        private UserFinder $userFinder,
        private EmailGatewayInterface $emailGateway,
        private string $template,
        private string $emailFrom,
        private LoginLinkHandlerInterface $loginLinkHandler,
    ) {
    }

    public function __invoke(SendLoginLink $command): void
    {
        $user = $this->userFinder->find($command->userId());
        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $this->emailGateway->send(
            $this->template,
            $user->email(),
            [
                'loginLinkUrl' => $this->loginLinkHandler->createLoginLink($user)->getUrl(),
                'name'         => $user->name(),
                'email'        => $command->email()->toString(),
            ],
            null,
            null,
            null,
            // add References header to prevent message threading in Gmail
            ['References' => $this->emailGateway->getReferencesEmail(Email::fromString($this->emailFrom))],
        );
    }
}
