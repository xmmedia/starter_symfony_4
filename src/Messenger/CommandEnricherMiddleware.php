<?php

declare(strict_types=1);

namespace App\Messenger;

use App\DataProvider\IssuerProvider;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class CommandEnricherMiddleware implements MiddlewareInterface
{
    /** @var IssuerProvider */
    private $issuerProvider;

    public function __construct(IssuerProvider $issuerProvider)
    {
        $this->issuerProvider = $issuerProvider;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        $message = $message->withAddedMetadata(
            'issuedBy',
            $this->issuerProvider->getIssuer()
        );

        // @todo this seems wrong to not pass any stamps, but atm there are none
        $newEnvelope = new Envelope($message);

        return $stack->next()->handle($newEnvelope, $stack);
    }
}
