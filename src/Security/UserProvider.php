<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Model\User\Command\UpgradePassword;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private readonly EntityUserProvider $entityUserProvider;

    public function __construct(
        ManagerRegistry $registry,
        private readonly MessageBusInterface $commandBus,
    ) {
        $this->entityUserProvider = new EntityUserProvider($registry, User::class, 'email');
    }

    /**
     * @param User|PasswordAuthenticatedUserInterface $user
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            return;
        }

        $this->commandBus->dispatch(
            UpgradePassword::forUser($user->userId(), $newHashedPassword),
        );

        $user->upgradePassword($newHashedPassword);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->entityUserProvider->loadUserByIdentifier($identifier);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->entityUserProvider->refreshUser($user);
    }

    public function supportsClass(string $class): bool
    {
        return $this->entityUserProvider->supportsClass($class);
    }
}
