<?php

declare(strict_types=1);

namespace App\Tests;

class ApplicationAvailabilityFunctionalTest extends WebTestCase
{
    use UsesFaker;

    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful(string $url, string $redirectUrl = null): void
    {
        $client = self::createClient();
        $client->request('GET', $url);

        if (null === $redirectUrl) {
            $this->assertResponseIsSuccessful();
        } else {
            $this->assertResponseRedirects(
                'http://localhost'.$redirectUrl,
                302,
            );
        }
    }

    public function urlProvider(): \Generator
    {
        $faker = $this->faker();

        yield ['/'];
        yield ['/login'];
        yield ['/admin', '/login'];
        yield ['/admin/users', '/login'];
        yield ['/profile', '/login'];
        yield ['/profile/password', '/login'];
        yield ['/activate/'.$faker->lexify(str_repeat('?', 10))];
        yield ['/recover/reset/'.$faker->lexify(str_repeat('?', 10))];
        yield ['/recover/initiate'];
    }
}
