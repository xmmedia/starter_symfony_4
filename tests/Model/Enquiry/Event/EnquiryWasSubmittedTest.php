<?php

declare(strict_types=1);

namespace App\Tests\Model\Enquiry\Event;

use App\Model\Enquiry\Event\EnquiryWasSubmitted;
use App\Tests\BaseTestCase;
use App\Tests\CanCreateEventFromArray;

class EnquiryWasSubmittedTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $enquiryId = $faker->enquiryId;
        $name = $faker->name;
        $email = $faker->emailVo;
        $message = $faker->asciify(str_repeat('*', 100));

        $event = EnquiryWasSubmitted::now($enquiryId, $name, $email, $message);

        $this->assertEquals($enquiryId, $event->enquiryId());
        $this->assertEquals($name, $event->name());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($message, $event->message());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $enquiryId = $faker->enquiryId;
        $name = $faker->name;
        $email = $faker->emailVo;
        $message = $faker->asciify(str_repeat('*', 100));

        /** @var EnquiryWasSubmitted $event */
        $event = $this->createEventFromArray(
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
