import gql from 'graphql-tag';

export const AuthLast = gql`
    query AuthLast {
        AuthLast {
            email
            error
        }
    }
`;
