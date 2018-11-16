<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\JsonRequestTransformerListener;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class JsonRequestTransformerListenerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var JsonRequestTransformerListener */
    private $listener;

    public function setUp()
    {
        $this->listener = new JsonRequestTransformerListener();
    }

    public function testSubscribedEvents(): void
    {
        $subscribed = JsonRequestTransformerListener::getSubscribedEvents();

        $this->assertEquals(
            10,
            $subscribed[key($subscribed)][1]
        );
    }

    /**
     * @dataProvider jsonContentTypes
     */
    public function testItTransformsRequestsWithJsonContentType($contentType): void
    {
        $data    = ['foo' => 'bar'];
        $request = $this->createRequest($contentType, true, \GuzzleHttp\json_encode($data));
        $event   = $this->createGetResponseEventMock($request);

        $this->listener->onKernelRequest($event);

        $this->assertEquals(
            $data,
            $event->getRequest()->request->all()
        );
    }

    public function jsonContentTypes(): array
    {
        return [
            ['application/json'],
            ['application/x-json'],
            ['application/json;charset=UTF-8'],
        ];
    }

    public function testItReturnsBadRequestResponseIfJsonInvalid(): void
    {
        $request = $this->createRequest('application/json', true, '{meh}');
        $event   = $this->createGetResponseEventMock($request);

        $event->shouldReceive('setResponse')
            ->with(Mockery::on(function ($argument) {
                if (!$argument instanceof Response) {
                    return false;
                }

                if (400 !== $argument->getStatusCode()) {
                    return false;
                }

                return true;
            }))
            ->once();

        $this->listener->onKernelRequest($event);
    }

    /**
     * @dataProvider notJsonContentTypes
     */
    public function testItDoesNotTransformOtherContentTypes($contentType): void
    {
        $request = $this->createRequest($contentType, true, 'some=body');
        $event   = $this->createGetResponseEventMock($request);

        $this->listener->onKernelRequest($event);

        $this->assertEquals($request, $event->getRequest());
    }

    public function testItDoesNotReplaceRequestDataIfThereIsNone(): void
    {
        $request = $this->createRequest('application/json', true, '');
        $event   = $this->createGetResponseEventMock($request);

        $this->listener->onKernelRequest($event);

        $this->assertEquals($request, $event->getRequest());
    }

    public function testItDoesNotReplaceRequestDataIfContentIsJsonNull(): void
    {
        $request = $this->createRequest('application/json', true, 'null');
        $event   = $this->createGetResponseEventMock($request);

        $this->listener->onKernelRequest($event);

        $this->assertEquals($request, $event->getRequest());
    }

    public function testItDoesNotReplaceRequestDataIfNotXmlHttp(): void
    {
        $request = $this->createRequest('application/json', false, \GuzzleHttp\json_encode([]));
        $event   = $this->createGetResponseEventMock($request);

        $this->listener->onKernelRequest($event);

        $this->assertEquals($request, $event->getRequest());
    }

    public function notJsonContentTypes(): array
    {
        return [
            ['application/x-www-form-urlencoded'],
            ['text/html'],
            ['text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'],
        ];
    }

    private function createRequest(string $contentType, bool $xmlHttp, string $body): Request
    {
        $server = [
            'CONTENT_TYPE' => $contentType,
        ];
        if ($xmlHttp) {
            $server['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        }

        $request = new Request([], [], [], [], [], $server, $body);

        return $request;
    }

    /**
     * @return GetResponseEvent|\Mockery\MockInterface
     */
    private function createGetResponseEventMock(Request $request): GetResponseEvent
    {
        $event = Mockery::mock(GetResponseEvent::class);
        $event->shouldReceive('getRequest')
            ->withNoArgs()
            ->andReturn($request);

        return $event;
    }
}
