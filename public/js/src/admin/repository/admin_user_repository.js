import api from '@/common/api';

export default {
    create (user) {
        return api.post('/api/admin/user/create', { user });
    },
};
