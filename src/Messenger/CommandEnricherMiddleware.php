<?php

declare(strict_types=1);

namespace App\Messenger;

use App\DataProvider\IssuerProvider;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;

class CommandEnricherMiddleware implements MiddlewareInterface
{
    /** @var IssuerProvider */
    private $issuerProvider;

    public function __construct(IssuerProvider $issuerProvider)
    {
        $this->issuerProvider = $issuerProvider;
    }

    /**
     * @param \App\Messaging\Command $message
     */
    public function handle($message, callable $next)
    {
        $message = $message->withAddedMetadata(
            'issuedBy',
            $this->issuerProvider->getIssuer()
        );

        $next($message);
    }
}
