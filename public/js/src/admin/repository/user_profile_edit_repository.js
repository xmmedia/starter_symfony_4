import api from '@/common/api';

export default {
    save (user) {
        return api.post('/api/user/profile/save', { user });
    },
};
