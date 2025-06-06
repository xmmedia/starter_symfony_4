<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\Attributes\DataProvider;

class ApplicationAvailabilityFunctionalTest extends WebTestCase
{
    use UsesFaker;

    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful(string $url, ?string $redirectUrl = null): void
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

    public static function urlProvider(): \Generator
    {
        $faker = self::makeFaker();

        yield ['/'];
        yield ['/login'];
        yield ['/admin', '/login'];
        yield ['/admin/users', '/login'];
        yield ['/profile', '/login'];
        yield ['/profile/password', '/login'];
        yield ['/activate/'.$faker->lexify(str_repeat('?', 10)), '/activate'];
        yield ['/recover/reset/'.$faker->lexify(str_repeat('?', 10)), '/recover/reset'];
        yield ['/verify/'.$faker->lexify(str_repeat('?', 10)), '/verify'];
        yield ['/recover/initiate'];
    }
}
