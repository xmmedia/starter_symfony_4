<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Xm\SymfonyBundle\Security\Voter\IsAdminTrait;

class PublishedVoter extends Voter
{
    use IsAdminTrait;

    public const VIEW = 'VIEW';

    /** @var AccessDecisionManagerInterface */
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject): bool
    {
        return \in_array($attribute, [self::VIEW])
            && $subject instanceof \App\Entity\Page;
    }

    /**
     * @param \App\Entity\Page $page
     */
    protected function voteOnAttribute($attribute, $page, TokenInterface $token): bool
    {
        if ($page->published()) {
            return true;
        }

        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return $this->isAdmin($token);
    }
}
