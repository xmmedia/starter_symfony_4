<?php

declare(strict_types=1);

namespace App\Security;

use App\Projection\User\UserFinder;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\PublicKeyCredentialUserEntity;

final readonly class WebAuthnUserEntityRepository implements PublicKeyCredentialUserEntityRepositoryInterface
{
    public function __construct(private UserFinder $userFinder)
    {
    }

    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        $user = $this->userFinder->findOneBy(['email' => mb_strtolower($username)]);

        if (null === $user) {
            return null;
        }

        return new PublicKeyCredentialUserEntity(
            $user->getUserIdentifier(),
            $user->userId()->toString(),
            $user->name() ?? $user->getUserIdentifier(),
        );
    }

    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        $user = $this->userFinder->find($userHandle);

        if (null === $user) {
            return null;
        }

        return new PublicKeyCredentialUserEntity(
            $user->getUserIdentifier(),
            $user->userId()->toString(),
            $user->name() ?? $user->getUserIdentifier(),
        );
    }
}
