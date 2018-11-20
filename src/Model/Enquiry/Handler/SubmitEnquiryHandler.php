<?php

declare(strict_types=1);

namespace App\Model\Enquiry\Handler;

use App\Model\Enquiry\Command\SubmitEnquiry;
use App\Model\Enquiry\Enquiry;
use App\Model\Enquiry\EnquiryList;

class SubmitEnquiryHandler
{
    /** @var EnquiryList */
    private $enquiryRepo;

    public function __construct(EnquiryList $enquiryRepo)
    {
        $this->enquiryRepo = $enquiryRepo;
    }

    public function __invoke(SubmitEnquiry $command): void
    {
        $enquiry = Enquiry::submit(
            $command->enquiryId(),
            $command->name(),
            $command->email(),
            $command->message()
        );

        $this->enquiryRepo->save($enquiry);
    }
}
