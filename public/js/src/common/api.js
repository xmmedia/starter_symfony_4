import axios from 'axios';

const instance = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
});

instance.interceptors.request.use((config) => {
    config.headers['x-csrf-token'] = window.App.$store.state.serverData.csrfToken;

    return config;
});

export default instance;
