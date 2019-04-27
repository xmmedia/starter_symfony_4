<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Util\Json;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class JsonRequestTransformerSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // priority matches FOS\RestBundle\EventListener\BodyListener
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->isJsonAjaxRequest($request)) {
            return;
        }

        if (empty($request->getContent())) {
            return;
        }

        if (!$this->transformJsonBody($request)) {
            $response = Response::create('Unable to parse request.', 400);
            $event->setResponse($response);
        }
    }

    private function transformJsonBody(Request $request): bool
    {
        try {
            $data = Json::decode($request->getContent());
        } catch (\JsonException $e) {
            return false;
        }

        if (null === $data) {
            return true;
        }

        $request->request = new ParameterBag($data);

        return true;
    }

    private function isJsonAjaxRequest(Request $request): bool
    {
        return $request->isXmlHttpRequest() && 'json' === $request->getContentType();
    }
}
