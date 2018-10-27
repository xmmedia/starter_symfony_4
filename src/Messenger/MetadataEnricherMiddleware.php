<?php

declare(strict_types=1);

namespace App\Messenger;

use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Security\Core\Security;

class MetadataEnricherMiddleware implements MiddlewareInterface
{
    /** @var Security */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
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
        if (null === $token = $this->security->getToken()) {
            return 'cli';
        }

        $user = $this->security->getUser();

        if (!$user) {
            return 'anonymous';
        }

        return $user->getUuid()->toString();
    }
}
