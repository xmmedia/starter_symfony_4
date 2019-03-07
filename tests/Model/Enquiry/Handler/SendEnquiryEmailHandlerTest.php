<?php

declare(strict_types=1);

namespace App\Tests\Model\Enquiry\Handler;

use App\Infrastructure\Email\EmailGatewayInterface;
use App\Model\EmailGatewayMessageId;
use App\Model\Enquiry\Command\SendEnquiryEmail;
use App\Model\Enquiry\Handler\SendEnquiryEmailHandler;
use App\Tests\BaseTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class SendEnquiryEmailHandlerTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = $this->faker();

        $adminEmail = $faker->email;

        $command = SendEnquiryEmail::with(
            $faker->name,
            $faker->emailVo,
            $faker->asciify(str_repeat('*', 25))
        );

        $emailGateway = Mockery::mock(EmailGatewayInterface::class);
        $emailGateway->shouldReceive('send')
            ->once()
            ->withArgs(function ($templateId, $email, $payload) use ($adminEmail) {
                $this->assertEquals($adminEmail, $email->toString());

                return true;
            })
            ->andReturn(EmailGatewayMessageId::fromString($faker->uuid))
        ;

        (new SendEnquiryEmailHandler(
            $emailGateway,
            $adminEmail
        ))(
            $command
        );
    }
}
