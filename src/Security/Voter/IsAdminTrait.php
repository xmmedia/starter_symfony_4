<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @property \Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface $decisionManager
 */
trait IsAdminTrait
{
    /**
     * True when the user has an admin role.
     */
    protected function isAdmin(TokenInterface $token)
    {
        return $this->decisionManager->decide($token, ['ROLE_ADMIN']);
    }
}
