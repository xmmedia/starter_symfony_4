<?php

declare(strict_types=1);

namespace App\Security;

use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;

/**
 * Temporary TOTP user wrapper used during setup confirmation and QR code generation.
 * Exposes the pending (not yet active) secret as the TOTP configuration so that
 * the code can be verified before the secret is promoted to active.
 */
final readonly class TotpPendingUser implements TwoFactorInterface
{
    public function __construct(
        private string $username,
        private string $pendingSecret,
    ) {
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return true;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->username;
    }

    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        return new TotpConfiguration($this->pendingSecret, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }
}
