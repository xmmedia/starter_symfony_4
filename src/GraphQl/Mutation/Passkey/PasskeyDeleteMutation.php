<?php

declare(strict_types=1);

namespace App\GraphQl\Mutation\Passkey;

use App\Repository\WebAuthnCredentialFinder;
use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class PasskeyDeleteMutation implements MutationInterface
{
    public function __construct(
        private WebAuthnCredentialFinder $credentialFinder,
        private Security $security,
    ) {
    }

    public function __invoke(Argument $args): array
    {
        $user = $this->security->getUser();
        $credential = $this->credentialFinder->findOneByIdAndUser($args['passkeyId'], $user);

        if (null === $credential) {
            throw new NotFoundHttpException('Passkey not found.');
        }

        if ($credential->user()->userId()->toString() !== $user->userId()->toString()) {
            throw new AccessDeniedHttpException();
        }

        $this->credentialFinder->remove($credential);

        return ['success' => true];
    }
}
