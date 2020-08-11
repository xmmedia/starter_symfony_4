<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

trait PwnedHttpClientMockTrait
{
    private function getPwnedHttpClient(): HttpClientInterface
    {
        return new MockHttpClient([
            new MockResponse('empty:1'),
        ]);
    }
}
