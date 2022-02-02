<?php

declare(strict_types=1);

namespace App\Tests\Model\Enquiry\Handler;

use App\Model\Enquiry\Command\SendEnquiryEmail;
use App\Model\Enquiry\Handler\SendEnquiryEmailHandler;
use App\Tests\BaseTestCase;
use Mockery;
use Xm\SymfonyBundle\Infrastructure\Email\EmailGatewayInterface;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

class SendEnquiryEmailHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $template = $faker->string(15);
        $adminEmail = $faker->email();

        $command = SendEnquiryEmail::with(
            $faker->name(),
            $faker->emailVo(),
            $faker->string(25),
        );

        $emailGateway = Mockery::mock(EmailGatewayInterface::class);
        $emailGateway->shouldReceive('send')
            ->once()
            ->withArgs(function ($templateArg, $email, $payload) use ($template, $adminEmail): bool {
                $this->assertSame($template, $templateArg);
                $this->assertEquals($adminEmail, $email->toString());

                return true;
            })
            ->andReturn(EmailGatewayMessageId::fromString($faker->uuid()))
        ;

        (new SendEnquiryEmailHandler(
            $emailGateway,
            $template,
            $adminEmail,
        ))(
            $command
        );
    }
}
