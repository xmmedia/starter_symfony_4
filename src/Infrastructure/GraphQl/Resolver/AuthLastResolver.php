<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthLastResolver implements ResolverInterface
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

    public function __invoke(): array
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
}
