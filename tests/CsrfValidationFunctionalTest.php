<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\SameOriginCsrfTokenManager;

/**
 * CsrfValidationListener wired into the app: the config, the routes & the lack of CORS it relies on.
 * The validation itself is covered by CsrfValidationListenerTest.
 */
class CsrfValidationFunctionalTest extends WebTestCase
{
    use UsesFaker;

    private const string QUERY = '{"query":"{ __typename }"}';
    // framework.csrf_protection.cookie_name
    private const string COOKIE_NAME = 'csrf-token';
    // the kernel browser's default host
    private const string HOST = 'localhost';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testValidToken(): void
    {
        $this->request(['HTTP_ORIGIN' => $this->origin(), 'HTTP_CSRF_TOKEN' => $this->token()]);

        $this->assertResponseIsSuccessful();
    }

    public function testBatchEndpoint(): void
    {
        $this->request(['HTTP_ORIGIN' => $this->origin(), 'HTTP_CSRF_TOKEN' => $this->token()], true);

        $this->assertResponseIsSuccessful();
    }

    public function testValidTokenHttps(): void
    {
        $token = $this->token(true);

        $this->request(['HTTPS' => 'on', 'HTTP_ORIGIN' => $this->origin(true), 'HTTP_CSRF_TOKEN' => $token]);

        $this->assertResponseIsSuccessful();
        $this->assertCookieCleared('__Host-'.self::COOKIE_NAME.'_'.$token);
    }

    /**
     * The token is valid, so it's the origin check that rejects it.
     */
    public function testCrossOrigin(): void
    {
        $this->request(['HTTP_ORIGIN' => $this->crossOrigin(), 'HTTP_CSRF_TOKEN' => $this->token()]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testBatchEndpointCrossOrigin(): void
    {
        $this->request(['HTTP_ORIGIN' => $this->crossOrigin()], true);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Fails if graphql isn't in framework.csrf_protection.stateless_token_ids: the session token
     * manager then validates the listener's fallback token against itself.
     */
    public function testNoOriginNoToken(): void
    {
        $this->request([]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * With check_header, the header alone passes a request with no origin info, so a cross-origin
     * page must never be allowed to send it. The app sends no CORS headers, so browsers block it.
     */
    public function testNoCorsHeaders(): void
    {
        $this->client->request(
            Request::METHOD_OPTIONS,
            '/graphql',
            server: [
                'HTTP_ORIGIN'                         => $this->crossOrigin(),
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD'  => Request::METHOD_POST,
                'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type, '.self::COOKIE_NAME,
            ],
        );

        foreach (array_keys($this->client->getResponse()->headers->all()) as $header) {
            $this->assertStringStartsNotWith('access-control-', $header);
        }
    }

    private function assertCookieCleared(string $name): void
    {
        foreach ($this->client->getResponse()->headers->getCookies() as $cookie) {
            if ($name === $cookie->getName()) {
                $this->assertTrue($cookie->isCleared());

                return;
            }
        }

        $this->fail(\sprintf('Cookie "%s" was not cleared.', $name));
    }

    private function request(array $server, bool $batch = false): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $batch ? '/graphql/batch' : '/graphql',
            server: $server + ['CONTENT_TYPE' => 'application/json'],
            content: $batch ? '['.self::QUERY.']' : self::QUERY,
        );
    }

    private function origin(bool $secure = false): string
    {
        return ($secure ? 'https' : 'http').'://'.self::HOST;
    }

    private function crossOrigin(): string
    {
        return 'https://'.$this->faker()->domainName();
    }

    /**
     * A token like csrfToken() in csrf.js makes (base64, so it can include + & /), with its cookie.
     */
    private function token(bool $secure = false): string
    {
        // base64 is 4 characters for every 3 bytes
        $token = base64_encode(random_bytes(intdiv(SameOriginCsrfTokenManager::TOKEN_MIN_LENGTH, 4) * 3));

        $this->client->getCookieJar()->set(new Cookie(
            ($secure ? '__Host-' : '').self::COOKIE_NAME.'_'.$token,
            self::COOKIE_NAME,
            null,
            '/',
            self::HOST,
            $secure,
        ));

        return $token;
    }
}
