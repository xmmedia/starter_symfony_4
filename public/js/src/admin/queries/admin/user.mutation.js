import gql from 'graphql-tag';

export const AdminUserCreateMutation = gql`mutation AdminUserCreate($user: AdminUserCreateInput!) {
    AdminUserCreate(user: $user) {
        id
    }
}`;

export const AdminUserUpdateMutation = gql`mutation AdminUserUpdate($user: AdminUserUpdateInput!) {
    AdminUserUpdate(user: $user) {
        id
    }
}`;

export const AdminUserActivateMutation =  gql`mutation AdminUserActivate($user: AdminUserActivateInput!) {
    AdminUserActivate(user: $user) {
        id
    }
}`;

export const AdminUserVerifyMutation =  gql`mutation AdminUserVerify($user: AdminUserInput!) {
    AdminUserVerify(user: $user) {
        id
    }
}`;

export const AdminUserSendResetMutation =  gql`mutation AdminUserSendReset($user: AdminUserInput!) {
    AdminUserSendReset(user: $user) {
        id
    }
}`;
