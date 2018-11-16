<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Demo\Command\DoDemo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class DemoController extends AbstractController
{
    /**
     * @Route("/demo", name="demo")
     */
    public function indexAction(MessageBusInterface $commandBus): Response
    {
        $commandBus->dispatch(DoDemo::now('message test'));

        return $this->render('default/index.html.twig');
    }
}
