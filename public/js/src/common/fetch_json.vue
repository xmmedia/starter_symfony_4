<script>
import api from './api';
import { logError } from './lib';

export default {
    props: {
        url: {
            type: String,
            required: true,
        },
        csrfToken: {
            type: String,
            required: true,
        },
        responseKey: {
            type: String,
            default: 'data',
        },
        reload: {
            type: Boolean,
            default: false,
        },
    },

    data () {
        return {
            data: [],
            loading: true,
            error: false,
        };
    },

    watch: {
        reload (val) {
            if (val) {
                this.load();
                this.$emit('reloaded');
            }
        },
    },

    mounted () {
        this.load();
    },

    methods: {
        async load () {
            try {
                this.loading = true;
                this.error = false;

                const response = await api.get(this.url, {
                    params: {
                        _csrf_token: this.csrfToken,
                    },
                });

                this.data = response.data[this.responseKey];

                this.loading = false;
                this.error = false;
            } catch (e) {
                logError(e);
                this.error = true;
                this.loading = false;
            }
        },
    },

    render () {
        return this.$scopedSlots.default({
            data: this.data,
            loading: this.loading,
            error: this.error,
        });
    },
}
</script>
