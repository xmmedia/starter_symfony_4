<?php

declare(strict_types=1);

namespace App\Tests\Model\Enquiry\Handler;

use App\Model\Email;
use App\Model\Enquiry\Command\SubmitEnquiry;
use App\Model\Enquiry\Enquiry;
use App\Model\Enquiry\EnquiryId;
use App\Model\Enquiry\EnquiryList;
use App\Model\Enquiry\Handler\SubmitEnquiryHandler;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class SubmitEnquiryHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = Faker\Factory::create();

        $enquiryId = EnquiryId::generate();
        $name = $faker->name;
        $email = Email::fromString($faker->email);
        $message = $faker->asciify(str_repeat('*', 100));

        $command = SubmitEnquiry::with($enquiryId, $name, $email, $message);

        $repo = Mockery::mock(EnquiryList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Enquiry::class));

        (new SubmitEnquiryHandler($repo))($command);
    }
}
