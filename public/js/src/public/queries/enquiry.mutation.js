import gql from 'graphql-tag';

export const SendEnquiry = gql`mutation SendEnquiry($enquiry: EnquiryInput!) {
    SendEnquiry(enquiry: $enquiry) {
        success
    }
}`;
