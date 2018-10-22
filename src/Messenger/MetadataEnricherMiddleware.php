<?php

declare(strict_types=1);

namespace App\Messenger;

use App\Commanding\Message;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MetadataEnricherMiddleware implements MiddlewareInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param \App\Commanding\Command $message
     */
    public function handle($message, callable $next)
    {
        $message = $message->withIssuedBy($this->getIssuer());

        $next($message);
    }

    private function getIssuer(): string
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return 'cli';
        }

        if (!$this->authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return 'anonymous';
        }

        return $this->tokenStorage->getToken()->getUser()->getUuid()->toString();
    }
}