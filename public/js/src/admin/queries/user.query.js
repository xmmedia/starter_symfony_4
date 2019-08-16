import gql from 'graphql-tag';

export const MeQuery = gql`
    query Me {
        Me {
            userId
            email
            name
            firstName
            lastName
            roles
        }
    }
`;

export const MeSimpleQuery = gql`
    query Me {
        Me {
            userId
        }
    }
`;

export const GetUsersQuery = gql`
    query GetUsers {
        Users {
            userId
            email
            name
            lastLogin
            loginCount
            roles
            verified
            active
        }
    }
`;

export const GetUserQuery = gql`
    query GetUser($userId: UUID!) {
        User(userId: $userId) {
            userId
            email
            roles
            firstName
            lastName
            verified
            active
        }
    }
`;
