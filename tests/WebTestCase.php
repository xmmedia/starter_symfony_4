<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\ORM\LoadDefaultFixtures;
use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class WebTestCase extends BaseWebTestCase
{
    /**
     * List of fixtures to load.
     *
     * @var array
     */
    protected $fixtureList = [
        LoadDefaultFixtures::class,
    ];

    /**
     * @var \Doctrine\Common\DataFixtures\ReferenceRepository
     */
    protected $fixtures;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function logIn($userRef = 'user-regular')
    {
        $user = $this->fixtures->getReference($userRef);

        $this->loginAs($user, 'main');
    }

    protected function assertPathInfoMatches(Client $client, $regExp)
    {
        $this->assertRegExp($regExp, $client->getRequest()->getPathInfo());
    }
}
