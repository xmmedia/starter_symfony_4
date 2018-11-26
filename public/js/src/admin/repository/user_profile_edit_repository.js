import api from '@/common/api';

export default {
    activate (data) {
        return api.post('/api/user/activate', { data });
    },

    save (user) {
        return api.post('/api/user/profile', { user });
    },
    password (user) {
        return api.post('/api/user/profile/password', { user });
    },
};
