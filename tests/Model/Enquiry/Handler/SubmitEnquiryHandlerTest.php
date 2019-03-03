<?php

declare(strict_types=1);

namespace App\Tests\Model\Enquiry\Handler;

use App\Model\Email;
use App\Model\Enquiry\Command\SubmitEnquiry;
use App\Model\Enquiry\Enquiry;
use App\Model\Enquiry\EnquiryList;
use App\Model\Enquiry\Handler\SubmitEnquiryHandler;
use App\Tests\BaseTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class SubmitEnquiryHandlerTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = $this->faker();

        $enquiryId = $faker->enquiryId;
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
