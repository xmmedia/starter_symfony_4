import gql from 'graphql-tag';

export const CreateUserMutation = gql`mutation CreateUser($user: UserCreateInput!) {
    CreateUser(user: $user) {
        id
        email
        active
    }
}`;

export const UpdateUserMutation = gql`mutation UpdateUser($user: UserUpdateInput!) {
    UpdateUser(user: $user) {
        id
    }
}`;
