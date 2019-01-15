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

export const UserVerify = gql`
    mutation UserVerify($token: String!, $newPassword: String!, $repeatPassword: String!) {
        UserVerify(token: $token, newPassword: $newPassword, repeatPassword: $repeatPassword) {
            success
        }
    }`;

export const UserUpdate = gql`mutation UserUpdate($user: UserUpdateInput!) {
    UserUpdate(user: $user) {
        id
    }
}`;

export const ChangePassword = gql`mutation ChangePassword($user: UserPasswordInput!) {
    ChangePassword(user: $user) {
        id
    }
}`;
