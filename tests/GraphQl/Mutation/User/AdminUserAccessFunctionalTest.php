<?php

declare(strict_types=1);

namespace App\Tests\GraphQl\Mutation\User;

use App\Entity\User;
use App\Model\User\Role;
use App\Projection\User\UserFinder;
use App\Security\UserProvider;
use App\Tests\UsesFaker;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\SameOriginCsrfTokenManager;
use Xm\SymfonyBundle\Util\Json;

/**
 * The admin user mutations taking a user ID are wired to AdminUserAccess,
 * so an admin can't act on a super admin. The rules are covered by AdminUserAccessTest.
 */
class AdminUserAccessFunctionalTest extends WebTestCase
{
    use UsesFaker;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        // keeps the mocked services set in the container between requests
        $this->client->disableReboot();
    }

    public static function mutationProvider(): \Generator
    {
        yield 'verify' => ['AdminUserVerify', 'userId'];
        yield 'send reset' => ['AdminUserSendReset', 'userId'];
        yield 'send activation' => ['AdminUserSendActivation', 'userId'];
        yield 'send login link' => ['AdminUserSendLoginLink', 'success'];
        yield 'delete' => ['AdminUserDelete', 'success'];
    }

    #[DataProvider('mutationProvider')]
    public function testAdminDeniedForSuperAdmin(string $mutation, string $field): void
    {
        $this->login($this->user(Role::ROLE_ADMIN));
        $superAdmin = $this->user(Role::ROLE_SUPER_ADMIN);

        $userFinder = \Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')->andReturn($superAdmin);
        self::getContainer()->set(UserFinder::class, $userFinder);

        $result = $this->query(
            \sprintf('mutation ($userId: UserId!) { %s(userId: $userId) { %s } }', $mutation, $field),
            ['userId' => $superAdmin->userId()->toString()],
        );

        $this->assertNull($result['data']);
        $this->assertSame('FORBIDDEN', $result['errors'][0]['extensions']['code']);
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

    private function query(string $query, array $variables): array
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
            content: Json::encode(['query' => $query, 'variables' => $variables]),
        );

        $this->assertResponseIsSuccessful();

        return Json::decode($this->client->getResponse()->getContent());
    }
}
