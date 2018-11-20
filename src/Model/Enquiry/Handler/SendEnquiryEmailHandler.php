<?php

declare(strict_types=1);

namespace App\Model\Enquiry\Handler;

use App\Model\Enquiry\Command\SendEnquiryEmail;

class SendEnquiryEmailHandler
{
    public function __invoke(SendEnquiryEmail $command): void
    {
        dump('Actually send the email.');
    }
}
