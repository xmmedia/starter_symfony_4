<?php

declare(strict_types=1);

namespace App\Tests\Model\Enquiry\Event;

use App\Model\Email;
use App\Model\Enquiry\EnquiryId;
use App\Model\Enquiry\Event\EnquiryWasSubmitted;
use App\Tests\CanCreateEvent;
use Faker;
use PHPUnit\Framework\TestCase;

class EnquiryWasSubmittedTest extends TestCase
{
    use CanCreateEvent;

    public function testOccur(): void
    {
        $faker = Faker\Factory::create();

        $enquiryId = EnquiryId::generate();
        $name = $faker->name;
        $email = Email::fromString($faker->email);
        $message = $faker->asciify(str_repeat('*', 100));

        $event = EnquiryWasSubmitted::now($enquiryId, $name, $email, $message);

        $this->assertEquals($enquiryId, $event->enquiryId());
        $this->assertEquals($name, $event->name());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($message, $event->message());
    }

    public function testFromArray(): void
    {
        $faker = Faker\Factory::create();

        $enquiryId = EnquiryId::generate();
        $name = $faker->name;
        $email = Email::fromString($faker->email);
        $message = $faker->asciify(str_repeat('*', 100));

        /** @var EnquiryWasSubmitted $event */
        $event = $this->createEvent(
            EnquiryWasSubmitted::class,
            $enquiryId->toString(),
            [
                'name'    => $name,
                'email'   => $email->toString(),
                'message' => $message,
            ]
        );

        $this->assertInstanceOf(EnquiryWasSubmitted::class, $event);

        $this->assertEquals($enquiryId, $event->enquiryId());
        $this->assertEquals($name, $event->name());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($message, $event->message());
    }
}
