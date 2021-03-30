<?php

declare(strict_types=1);

namespace App\Tests\Model\Enquiry\Command;

use App\Model\Enquiry\Command\SubmitEnquiry;
use App\Model\Enquiry\Name;
use App\Tests\BaseTestCase;

class SubmitEnquiryTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $enquiryId = $faker->enquiryId();
        $name = Name::fromString($faker->name());
        $email = $faker->emailVo();
        $message = $faker->asciify(str_repeat('*', 100));

        $command = SubmitEnquiry::with($enquiryId, $name, $email, $message);

        $this->assertTrue($enquiryId->sameValueAs($command->enquiryId()));
        $this->assertEquals($name, $command->name());
        $this->assertTrue($email->sameValueAs($command->email()));
        $this->assertEquals($message, $command->message());
    }
}
