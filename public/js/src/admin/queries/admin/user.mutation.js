import gql from 'graphql-tag';

export const AdminCreateUserMutation = gql`mutation AdminCreateUser($user: AdminUserCreateInput!) {
    AdminCreateUser(user: $user) {
        id
    }
}`;

export const AdminUpdateUserMutation = gql`mutation AdminUpdateUser($user: AdminUserUpdateInput!) {
    AdminUpdateUser(user: $user) {
        id
    }
}`;

export const AdminActivateUserMutation =  gql`mutation AdminActivateUser($user: AdminUserActivateInput!) {
    AdminActivateUser(user: $user) {
        id
    }
}`;

export const AdminVerifyUserMutation =  gql`mutation AdminVerifyUser($user: AdminUserInput!) {
    AdminVerifyUser(user: $user) {
        id
    }
}`;

export const AdminSendResetToUserMutation =  gql`mutation AdminSendResetToUser($user: AdminUserInput!) {
    AdminSendResetToUser(user: $user) {
        id
    }
}`;
