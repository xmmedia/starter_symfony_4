import gql from 'graphql-tag';

export const AdminUserAddMutation = gql`mutation AdminUserAdd($user: AdminUserAddInput!) {
    AdminUserAdd(user: $user) {
        userId
    }
}`;

export const AdminUserUpdateMutation = gql`mutation AdminUserUpdate($user: AdminUserUpdateInput!) {
    AdminUserUpdate(user: $user) {
        userId
    }
}`;

export const AdminUserActivateMutation =  gql`mutation AdminUserActivate($user: AdminUserActivateInput!) {
    AdminUserActivate(user: $user) {
        userId
    }
}`;

export const AdminUserVerifyMutation =  gql`mutation AdminUserVerify($user: AdminUserInput!) {
    AdminUserVerify(user: $user) {
        userId
    }
}`;

export const AdminUserSendResetMutation =  gql`mutation AdminUserSendReset($user: AdminUserInput!) {
    AdminUserSendReset(user: $user) {
        userId
    }
}`;
