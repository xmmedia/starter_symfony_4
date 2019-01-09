import api from '@/common/api';

export default {
    get (userId) {
        return api.get('/api/admin/user/'+userId);
    },

    create (user) {
        return api.post('/api/admin/user/create', { user });
    },
    update (user) {
        return api.post('/api/admin/user/'+user.id, { user });
    },

    activate (userId) {
        return api.post('/api/admin/user/'+userId+'/activate');
    },
    deactivate (userId) {
        return api.post('/api/admin/user/'+userId+'/deactivate');
    },
    verify (userId) {
        return api.post('/api/admin/user/'+userId+'/verify');
    },
    sendReset (userId) {
        return api.post('/api/admin/user/'+userId+'/send-reset');
    },
};
