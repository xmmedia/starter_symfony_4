import api from '@/common/api';

export default {
    recoverInitiate (data) {
        return api.post('/api/user/recover/initiate', { data });
    },
    recoverReset (data) {
        return api.post('/api/user/recover/reset', { data });
    },
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
