<?php

declare(strict_types=1);

namespace App\Model\Enquiry\Handler;

use App\Model\Enquiry\Command\SubmitEnquiryForm;
use App\Model\Enquiry\Enquiry;
use App\Model\Enquiry\EnquiryList;

class SubmitEnquiryFormHandler
{
    /** @var EnquiryList */
    private $enquiryRepo;

    public function __construct(EnquiryList $enquiryRepo)
    {
        $this->enquiryRepo = $enquiryRepo;
    }

    public function __invoke(SubmitEnquiryForm $command): void
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
