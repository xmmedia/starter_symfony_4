import adminUser from './admin_user_repository';
import userProfileEdit from './user_profile_edit_repository';

const repositories = {
    adminUser,
    userProfileEdit,
};

export const repositoryFactory = {
    get: (name) => repositories[name],
};
