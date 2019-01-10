import gql from 'graphql-tag';

export const CreateUserMutation = gql`mutation CreateUser($user: UserCreateInput!) {
    CreateUser(user: $user) {
        id
    }
}`;

export const UpdateUserMutation = gql`mutation UpdateUser($user: UserUpdateInput!) {
    UpdateUser(user: $user) {
        id
    }
}`;

export const ActivateUserMutation =  gql`mutation ActivateUser($user: UserActivateInput!) {
    ActivateUser(user: $user) {
        id
    }
}`;

export const VerifyUserMutation =  gql`mutation VerifyUser($user: UserInput!) {
    VerifyUser(user: $user) {
        id
    }
}`;

export const SendResetToUserMutation =  gql`mutation SendResetToUser($user: UserInput!) {
    SendResetToUser(user: $user) {
        id
    }
}`;
