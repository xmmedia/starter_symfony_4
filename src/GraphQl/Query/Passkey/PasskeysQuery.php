<?php

declare(strict_types=1);

namespace App\GraphQl\Query\Passkey;

use App\Entity\User;
use App\Repository\WebAuthnCredentialFinder;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class PasskeysQuery implements QueryInterface
{
    public function __construct(private WebAuthnCredentialFinder $credentialFinder)
    {
    }

    public function __invoke(User $user): array
    {
        return $this->credentialFinder->findAllForUser($user);
    }
}
