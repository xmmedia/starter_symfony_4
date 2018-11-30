<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\Model\Email;
use App\Model\Enquiry\Command\SendEnquiryEmail;
use App\Model\Enquiry\EnquiryId;
use App\Model\Enquiry\Event\EnquiryWasSubmitted;
use App\ProcessManager\EnquirySubmittedProcessManager;
use App\Tests\BaseTestCase;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class EnquirySubmittedProcessManagerTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = Faker\Factory::create();

        $enquiryId = EnquiryId::generate();
        $name = $faker->name;
        $email = Email::fromString($faker->email);
        $message = $faker->asciify(str_repeat('*', 100));

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(SendEnquiryEmail::class))
            ->andReturn(new Envelope(new \StdClass()));

        $event = EnquiryWasSubmitted::now($enquiryId, $name, $email, $message);

        (new EnquirySubmittedProcessManager($commandBus))($event);
    }
}
