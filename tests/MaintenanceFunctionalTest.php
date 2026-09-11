<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceGate;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceMode;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenancePage;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceSettings;

/**
 * The bundle's maintenance mode, with this project's config & template override.
 */
class MaintenanceFunctionalTest extends WebTestCase
{
    use UsesFaker;

    private KernelBrowser $client;
    private MaintenanceMode $maintenanceMode;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->maintenanceMode = self::getContainer()->get(MaintenanceMode::class);
    }

    protected function tearDown(): void
    {
        $this->maintenanceMode->disable();

        parent::tearDown();
    }

    public function testPage(): void
    {
        $message = $this->faker()->sentence();
        $settings = new MaintenanceSettings($message);
        // as app:maintenance does
        $this->maintenanceMode->enable($settings, self::getContainer()->get(MaintenancePage::class)->render($settings));

        $this->client->request(Request::METHOD_GET, '/login');

        $this->assertResponseStatusCodeSame(Response::HTTP_SERVICE_UNAVAILABLE);
        $this->assertResponseHeaderSame(MaintenanceGate::HEADER, '1');
        $this->assertSelectorTextContains('h1', 'We\'re doing some maintenance');
        $this->assertSelectorTextContains('main', $message);
        // from the project's override
        $this->assertSelectorExists('img[src="/images/logo.svg"]');
    }

    public function testGraphQl(): void
    {
        $this->maintenanceMode->enable(new MaintenanceSettings());

        $this->client->request(Request::METHOD_POST, '/graphql/batch', content: '[{"query":"{ Me { userId } }"}]');

        $this->assertResponseStatusCodeSame(Response::HTTP_SERVICE_UNAVAILABLE);
        $this->assertSame(
            MaintenanceGate::ERROR_CODE,
            json_decode($this->client->getResponse()->getContent(), true)['errors'][0]['extensions']['code'],
        );
    }

    public function testAllowedIp(): void
    {
        $this->maintenanceMode->enable(new MaintenanceSettings(null, null, ['127.0.0.1']));

        $this->client->request(Request::METHOD_GET, '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('[role="status"]', 'Maintenance mode is on');
    }

    public function testKey(): void
    {
        $key = MaintenanceSettings::generateKey();
        $this->maintenanceMode->enable(new MaintenanceSettings(null, null, [], $key));

        $this->client->request(Request::METHOD_GET, '/login?'.MaintenanceGate::KEY_PARAMETER.'='.$key);

        $this->assertResponseRedirects('http://localhost/login');
        $this->assertBrowserCookieValueSame(MaintenanceGate::KEY_COOKIE, $key);

        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('[role="status"]', 'Maintenance mode is on');
    }

    public function testCommandRendersThePage(): void
    {
        $message = $this->faker()->sentence();

        new CommandTester(new Application(self::$kernel)->find('app:maintenance'))
            ->execute(['action' => 'on', '--message' => $message]);

        $this->assertStringContainsString($message, $this->maintenanceMode->page());
        // from the project's override
        $this->assertStringContainsString('src="/images/logo.svg"', $this->maintenanceMode->page());
    }
}
