import gql from 'graphql-tag';

export const GetUsersQuery = gql`query GetUsers {
    Users {
        id
        email
        name
        lastLogin
        loginCount
        roles
        verified
        active
    }
}`;

export const GetUserQuery = gql`query GetUser($id: UUID!) {
    User(id: $id) {
        id
        email
        roles
        firstName
        lastName
        verified
        active
    }
}`;
