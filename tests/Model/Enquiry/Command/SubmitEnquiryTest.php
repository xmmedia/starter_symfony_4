<?php

declare(strict_types=1);

namespace App\Tests\Model\Enquiry\Command;

use App\Model\Email;
use App\Model\Enquiry\Command\SubmitEnquiry;
use App\Model\Enquiry\EnquiryId;
use Faker;
use PHPUnit\Framework\TestCase;

class SubmitEnquiryTest extends TestCase
{
    public function test(): void
    {
        $faker = Faker\Factory::create();

        $enquiryId = EnquiryId::generate();
        $name = $faker->name;
        $email = Email::fromString($faker->email);
        $message = $faker->asciify(str_repeat('*', 100));

        $command = SubmitEnquiry::with($enquiryId, $name, $email, $message);

        $this->assertTrue($enquiryId->sameValueAs($command->enquiryId()));
        $this->assertEquals($name, $command->name());
        $this->assertTrue($email->sameValueAs($command->email()));
        $this->assertEquals($message, $command->message());
    }
}
