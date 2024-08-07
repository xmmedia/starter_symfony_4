<?php

declare(strict_types=1);

namespace App\GraphQl\Query\User;

use App\Controller\SecurityController;
use App\Util\Assert;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;
use Xm\SymfonyBundle\Util\PasswordStrengthInterface;

final readonly class UserRecoverResetPasswordStrengthQuery implements QueryInterface
{
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private RequestInfoProvider $requestProvider,
        private ?PasswordStrengthInterface $passwordStrength = null,
        private ?HttpClientInterface $pwnedHttpClient = null,
    ) {
    }

    public function __invoke(#[\SensitiveParameter] string $newPassword): array
    {
        $session = $this->requestProvider->currentRequest()->getSession();
        $token = $session->get(SecurityController::TOKEN_SESSION_KEY);

        if (!$token) {
            return [
                'allowed' => true,
            ];
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface) {
            return [
                'allowed' => true,
            ];
        }

        try {
            // done here because we need the user entity
            Assert::passwordAllowed(
                $newPassword,
                $user->email(),
                $user->firstName(),
                $user->lastName(),
                null,
                $this->passwordStrength,
                $this->pwnedHttpClient,
            );
        } catch (\InvalidArgumentException) {
            return [
                'allowed' => false,
            ];
        }

        return [
            'allowed' => true,
        ];
    }
}
