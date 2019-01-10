import api from '@/common/api';

export default {
    activate (data) {
        return api.post('/api/user/activate', { data });
    },
};
