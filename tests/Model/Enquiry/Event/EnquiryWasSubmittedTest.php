<?php

declare(strict_types=1);

namespace App\Tests\Model\Enquiry\Event;

use App\Model\Email;
use App\Model\Enquiry\EnquiryId;
use App\Model\Enquiry\Event\EnquiryWasSubmitted;
use Faker;
use PHPUnit\Framework\TestCase;

class EnquiryWasSubmittedTest extends TestCase
{
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

    public function testFromARray(): void
    {
        $faker = Faker\Factory::create();

        $enquiryId = EnquiryId::generate();
        $name = $faker->name;
        $email = Email::fromString($faker->email);
        $message = $faker->asciify(str_repeat('*', 100));

        $event = EnquiryWasSubmitted::fromArray([

            'message_name' => EnquiryWasSubmitted::class,
            'uuid' => $faker->uuid,
            'payload' => [

                'name'    => $name,
                'email'   => $email->toString(),
                'message' => $message,
            ],
            'metadata' => [
                '_aggregate_id' => $enquiryId->toString(),
            ],
            'created_at' => new \DateTimeImmutable(),
        ]);

        $this->assertInstanceOf(EnquiryWasSubmitted::class, $event);

        $this->assertEquals( $enquiryId, $event->enquiryId());
        $this->assertEquals($name, $event->name());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($message, $event->message());
    }
}
