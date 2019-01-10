import userProfileEdit from './user_profile_edit_repository';

const repositories = {
    userProfileEdit,
};

export const repositoryFactory = {
    get: (name) => repositories[name],
};
