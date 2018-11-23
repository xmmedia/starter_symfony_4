import api from '@/common/api';

export default {
    list () {
        return api.get('/api/admin/users');
    },
    get (userId) {
        return api.get('/api/admin/user/'+userId);
    },
    create (user) {
        return api.post('/api/admin/user/create', { user });
    },
    update (user) {
        return api.post('/api/admin/user/'+user.id, { user });
    },
};
