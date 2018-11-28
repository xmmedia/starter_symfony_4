<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\Zones;
use Webmozart\Assert\Assert;

class Cloudflare
{
    /** @var string */
    private $cloudflareZone;

    /** @var string */
    private $cloudflareUsername;

    /** @var string */
    private $cloudflareApiKey;

    /** @var Guzzle */
    private $adaptor;

    public function __construct(
        string $cloudflareZone,
        string $cloudflareUsername,
        string $cloudflareApiKey
    ) {
        $this->cloudflareZone = $cloudflareZone;
        $this->cloudflareUsername = $cloudflareUsername;
        $this->cloudflareApiKey = $cloudflareApiKey;
    }

    public function clearCache(): bool
    {
        $this->connect();

        $zones = new Zones($this->adaptor);

        return $zones->cachePurgeEverything($this->cloudflareZone);
    }

    private function connect(): void
    {
        if (null !== $this->adaptor) {
            return;
        }

        Assert::notEmpty($this->cloudflareZone, 'The Cloudflare zone env var is not set.');
        Assert::notEmpty($this->cloudflareUsername, 'The Cloudflare username env var is not set.');
        Assert::notEmpty($this->cloudflareApiKey, 'The Cloudflare api key env var is not set.');

        $key = new APIKey(
            $this->cloudflareUsername,
            $this->cloudflareApiKey
        );
        $this->adaptor = new Guzzle($key);
    }
}
