import api from '@/common/api';

export default {
    save (user) {
        return api.post('/api/user/profile', { user });
    },
    password (user) {
        return api.post('/api/user/profile/password', { user });
    },
};
