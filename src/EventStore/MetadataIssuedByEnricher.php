<?php

declare(strict_types=1);

namespace App\EventStore;

use App\DataProvider\IssuerProvider;
use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Metadata\MetadataEnricher;

class MetadataIssuedByEnricher implements MetadataEnricher
{
    /** @var IssuerProvider */
    private $issuerProvider;

    public function __construct(IssuerProvider $issuerProvider)
    {
        $this->issuerProvider = $issuerProvider;
    }

    public function enrich(Message $message): Message
    {
        return $message->withAddedMetadata(
            'issuedBy',
            $this->issuerProvider->getIssuer()
        );
    }
}
