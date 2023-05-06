<?php

declare(strict_types=1);

namespace App\Model\Enquiry\Handler;

use App\Model\Enquiry\Command\SubmitEnquiry;
use App\Model\Enquiry\Enquiry;
use App\Model\Enquiry\EnquiryList;

readonly class SubmitEnquiryHandler
{
    public function __construct(private EnquiryList $enquiryRepo)
    {
    }

    public function __invoke(SubmitEnquiry $command): void
    {
        $enquiry = Enquiry::submit(
            $command->enquiryId(),
            $command->name(),
            $command->email(),
            $command->message(),
        );

        $this->enquiryRepo->save($enquiry);
    }
}
