import adminUser from './admin_user_repository';
import userProfile from './user_profile_repository';

const repositories = {
    adminUser,
    userProfile,
};

export const repositoryFactory = {
    get: (name) => repositories[name],
};
