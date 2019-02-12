<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatorInterface;

class AuthLastResolver implements ResolverInterface, AliasedInterface
{
    /** @var AuthenticationUtils */
    private $authenticationUtils;

    /** @var TranslatorInterface */
    private $trans;

    public function __construct(
        AuthenticationUtils $authenticationUtils,
        TranslatorInterface $trans
    ) {
        $this->authenticationUtils = $authenticationUtils;
        $this->trans = $trans;
    }

    public function get(): array
    {
        $errorMsg = null;
        if ($error = $this->authenticationUtils->getLastAuthenticationError()) {
            $errorMsg = $this->trans->trans(
                $error->getMessageKey(),
                $error->getMessageData(),
                'security'
            );
        }

        return [
            'email' => $this->authenticationUtils->getLastUsername(),
            'error' => $errorMsg,
        ];
    }

    public static function getAliases(): array
    {
        return [
            'get' => 'app.graphql.resolver.auth_last',
        ];
    }
}
