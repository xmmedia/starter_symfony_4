<?php

declare(strict_types=1);

namespace App\Security;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Condition\TwoFactorConditionInterface;
use Webauthn\Bundle\Security\Http\Authenticator\Passport\Credentials\WebauthnCredentials;

/**
 * Skips TOTP 2FA when the user authenticated via a passkey.
 * Passkeys include user verification (biometric/PIN), so they count as strong auth.
 */
final class PasskeySkips2FA implements TwoFactorConditionInterface
{
    public function shouldPerformTwoFactorAuthentication(AuthenticationContextInterface $context): bool
    {
        return !$context->getPassport()->hasBadge(WebauthnCredentials::class);
    }
}
