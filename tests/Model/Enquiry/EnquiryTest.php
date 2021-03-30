<?php

declare(strict_types=1);

namespace App\Tests\Model\Enquiry;

use App\Model\Enquiry\Enquiry;
use App\Model\Enquiry\Event\EnquiryWasSubmitted;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeAr;

class EnquiryTest extends BaseTestCase
{
    public function testSubmit(): void
    {
        $faker = $this->faker();

        $enquiryId = $faker->enquiryId();
        $name = $faker->name();
        $email = $faker->emailVo();
        $message = $faker->asciify(str_repeat('*', 100));

        $enquiry = Enquiry::submit($enquiryId, $name, $email, $message);

        $this->assertInstanceOf(Enquiry::class, $enquiry);

        $events = $this->popRecordedEvent($enquiry);

        $this->assertRecordedEvent(EnquiryWasSubmitted::class, [
            'email'   => $email->toString(),
            'name'    => $name,
            'message' => $message,
        ], $events);

        $this->assertCount(1, $events);

        $this->assertEquals($enquiryId, $enquiry->enquiryId());
    }

    public function testSameIdentityAs(): void
    {
        $faker = $this->faker();

        $enquiryId = $faker->enquiryId();
        $name = $faker->name();
        $email = $faker->emailVo();
        $message = $faker->asciify(str_repeat('*', 100));

        $enquiry1 = Enquiry::submit($enquiryId, $name, $email, $message);
        $enquiry2 = Enquiry::submit($enquiryId, $name, $email, $message);

        $this->assertTrue($enquiry1->sameIdentityAs($enquiry2));
    }

    public function testSameIdentityAsFalse(): void
    {
        $faker = $this->faker();

        $name = $faker->name();
        $email = $faker->emailVo();
        $message = $faker->asciify(str_repeat('*', 100));

        $enquiry1 = Enquiry::submit($faker->enquiryId(), $name, $email, $message);
        $enquiry2 = Enquiry::submit($faker->enquiryId(), $name, $email, $message);

        $this->assertFalse($enquiry1->sameIdentityAs($enquiry2));
    }

    public function testSameIdentityAsDiffClass(): void
    {
        $faker = $this->faker();

        $enquiry = Enquiry::submit(
            $faker->enquiryId(),
            $faker->name(),
            $faker->emailVo(),
            $faker->string(100)
        );

        $this->assertFalse($enquiry->sameIdentityAs(FakeAr::create()));
    }
}
