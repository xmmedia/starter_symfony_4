<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use App\Tests\UsesFaker;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Note: we don't check for empty tokens because the main routes (where the Vue app is rendered)
 * don't have the token and thus will respond successfully when the token is skipped.
 */
class SecurityControllerTest extends WebTestCase
{
    use UsesFaker;

    public function testLoginWhenNotAuthenticated(): void
    {
        $client = self::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('html');
    }

    public function testLoginLinkThrowsException(): void
    {
        $controller = new SecurityController();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Shouldn\'t have gotten to the login link action');

        $controller->loginLink();
    }

    public function testActivateRedirect(): void
    {
        $client = self::createClient();
        $token = $this->faker()->lexify(str_repeat('?', 32));

        $client->request('GET', '/activate/'.$token);

        $this->assertResponseRedirects('/activate', 302);

        $session = $client->getRequest()->getSession();
        $this->assertEquals($token, $session->get(SecurityController::TOKEN_SESSION_KEY));
    }

    public function testVerifyRedirect(): void
    {
        $client = self::createClient();
        $token = $this->faker()->lexify(str_repeat('?', 32));

        $client->request('GET', '/verify/'.$token);

        $this->assertResponseRedirects('/verify');

        $session = $client->getRequest()->getSession();
        $this->assertEquals($token, $session->get(SecurityController::TOKEN_SESSION_KEY));
    }

    public function testResetRedirect(): void
    {
        $client = self::createClient();
        $token = $this->faker()->lexify(str_repeat('?', 32));

        $client->request('GET', '/recover/reset/'.$token);

        $this->assertResponseRedirects('/recover/reset');

        $session = $client->getRequest()->getSession();
        $this->assertEquals($token, $session->get(SecurityController::TOKEN_SESSION_KEY));
    }
}
