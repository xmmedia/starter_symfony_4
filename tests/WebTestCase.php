<?php

declare(strict_types=1);

namespace App\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class WebTestCase extends BaseWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function assertPathInfoMatches(Client $client, $regExp)
    {
        $this->assertMatchesRegularExpression(
            $regExp,
            $client->getRequest()->getPathInfo()
        );
    }
}
