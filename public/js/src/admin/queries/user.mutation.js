import gql from 'graphql-tag';

export const UpdateUser = gql`mutation UpdateUser($user: UserUpdateInput!) {
    UpdateUser(user: $user) {
        id
    }
}`;

export const ChangePassword = gql`mutation ChangePassword($user: UserPasswordInput!) {
    ChangePassword(user: $user) {
        id
    }
}`;
