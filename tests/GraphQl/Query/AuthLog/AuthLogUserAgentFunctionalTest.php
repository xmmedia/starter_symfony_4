<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Query\AuthLog;

use App\Entity\AuthLog;
use App\Entity\User;
use App\Model\User\Role;
use App\Projection\AuthLog\AuthLogFinder;
use App\Security\UserProvider;
use App\Tests\UsesFaker;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\SameOriginCsrfTokenManager;
use Xm\SymfonyBundle\Util\Json;

/**
 * The parsed user agent through a GraphQL request, the way the admin auth log lists use it.
 */
class AuthLogUserAgentFunctionalTest extends WebTestCase
{
    use UsesFaker;

    private const string USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        // keeps the mocked services set in the container between requests
        $this->client->disableReboot();
    }

    public function testParsedUserAgent(): void
    {
        $this->loginAdmin();
        $this->authLogs([$this->authLog(self::USER_AGENT)]);

        $result = $this->query(
            '{ AuthLogs { userAgent parsedUserAgent { browser browserVersion platform summary } } }',
        );

        $this->assertSame(
            [
                'userAgent'       => self::USER_AGENT,
                'parsedUserAgent' => [
                    'browser'        => 'Chrome',
                    'browserVersion' => '142.0.0.0',
                    'platform'       => 'Macintosh',
                    'summary'        => 'Chrome 142 / Macintosh',
                ],
            ],
            $result['data']['AuthLogs'][0],
        );
    }

    public function testNoUserAgent(): void
    {
        $this->loginAdmin();
        $this->authLogs([$this->authLog(null)]);

        $result = $this->query('{ AuthLogs { parsedUserAgent { summary } } }');

        $this->assertNull($result['data']['AuthLogs'][0]['parsedUserAgent']);
    }

    public function testDeniedForNonAdmin(): void
    {
        $this->login($this->user(Role::ROLE_USER));
        $this->authLogs([$this->authLog(self::USER_AGENT)]);

        $result = $this->query('{ AuthLogs { parsedUserAgent { summary } } }');

        $this->assertEmpty($result['data']['AuthLogs'] ?? null);
        // a denied list field is reported as a warning, not an error
        $this->assertSame(['AuthLogs'], $result['extensions']['warnings'][0]['path']);
        $this->assertSame('Access denied to this field.', $result['extensions']['warnings'][0]['message']);
    }

    private function loginAdmin(): void
    {
        $this->login($this->user(Role::ROLE_ADMIN));
    }

    private function login(User $user): void
    {
        // the firewall refreshes the user from the read model on each request
        $userProvider = \Mockery::mock(UserProvider::class);
        $userProvider->shouldReceive('refreshUser')->andReturn($user);
        $userProvider->shouldReceive('supportsClass')->andReturnTrue();
        self::getContainer()->set(UserProvider::class, $userProvider);

        $this->client->loginUser($user);
    }

    private function user(Role $role): User
    {
        $user = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('userId')->setValue($user, $this->faker()->userId()->uuid());
        $reflection->getProperty('email')->setValue($user, $this->faker()->email());
        $reflection->getProperty('active')->setValue($user, true);
        $reflection->getProperty('verified')->setValue($user, true);
        $reflection->getProperty('roles')->setValue($user, [$role->value]);

        return $user;
    }

    private function authLog(?string $userAgent): AuthLog
    {
        $authLog = new AuthLog();
        $reflection = new \ReflectionClass(AuthLog::class);
        $reflection->getProperty('userAgent')->setValue($authLog, $userAgent);

        return $authLog;
    }

    /**
     * @param AuthLog[] $authLogs
     */
    private function authLogs(array $authLogs): void
    {
        $authLogFinder = \Mockery::mock(AuthLogFinder::class);
        $authLogFinder->shouldReceive('findByFilters')->andReturn($authLogs);
        self::getContainer()->set(AuthLogFinder::class, $authLogFinder);
    }

    private function query(string $query): array
    {
        // a token & cookie like csrfToken() in csrf.js makes
        $token = base64_encode(random_bytes(intdiv(SameOriginCsrfTokenManager::TOKEN_MIN_LENGTH, 4) * 3));
        $this->client->getCookieJar()->set(new Cookie('csrf-token_'.$token, 'csrf-token', null, '/', 'localhost'));

        $this->client->request(
            Request::METHOD_POST,
            '/graphql',
            server: [
                'CONTENT_TYPE'    => 'application/json',
                'HTTP_ORIGIN'     => 'http://localhost',
                'HTTP_CSRF_TOKEN' => $token,
            ],
            content: Json::encode(['query' => $query]),
        );

        $this->assertResponseIsSuccessful();

        return Json::decode($this->client->getResponse()->getContent());
    }
}
