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

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private EntityUserProvider $entityUserProvider;
    private MessageBusInterface $commandBus;

    public function __construct(
        ManagerRegistry $registry,
        MessageBusInterface $commandBus
    ) {
        $this->commandBus = $commandBus;

        $this->entityUserProvider = new EntityUserProvider($registry, User::class, 'email');
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

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->entityUserProvider->loadUserByIdentifier($identifier);
    }

    public function loadUserByUsername(string $username)
    {
        return $this->entityUserProvider->loadUserByUsername($username);
    }

    public function refreshUser(UserInterface $user)
    {
        return $this->entityUserProvider->refreshUser($user);
    }

    public function supportsClass(string $class)
    {
        return $this->entityUserProvider->supportsClass($class);
    }
}
