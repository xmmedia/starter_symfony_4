<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Model\User\Command\UpgradePassword;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider extends EntityUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    public function __construct(
        ManagerRegistry $registry,
        MessageBusInterface $commandBus
    ) {
        parent::__construct($registry, User::class, 'email');

        $this->commandBus = $commandBus;
    }

    /**
     * @param User|UserInterface $user
     */
    public function upgradePassword(
        UserInterface $user,
        string $newEncodedPassword
    ): void {
        if (!$user instanceof User) {
            return;
        }

        $this->commandBus->dispatch(
            UpgradePassword::forUser($user->userId(), $newEncodedPassword)
        );

        $user->upgradePassword($newEncodedPassword);
    }
}
