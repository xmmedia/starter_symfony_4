import gql from 'graphql-tag';

export const UserRecoverInitiate = gql`mutation UserRecoverInitiate($email: String!) {
    UserRecoverInitiate(email: $email) {
        success
    }
}`;

export const UserRecoverReset = gql`
    mutation UserRecoverReset($token: String!, $newPassword: String!, $repeatPassword: String!) {
        UserRecoverReset(token: $token, newPassword: $newPassword, repeatPassword: $repeatPassword) {
            success
        }
    }`;

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
