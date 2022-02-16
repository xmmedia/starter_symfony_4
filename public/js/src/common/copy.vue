<template>
    <button ref="button"
            :title="title"
            class="text-gray-400 focus:text-gray-600 hover:text-gray-600"
            type="button">
        <admin-icon icon="copy" class="w-4 h-4 fill-current" />
    </button>
</template>

<script>
import ClipboardJs from 'clipboard';

export default {
    props: {
        text: {
            type: String,
            required: true,
        },
        title: {
            type: String,
            default: null,
        },
    },

    data () {
        return {
            clipboard: null,
        };
    },

    mounted () {
        this.clipboard = new ClipboardJs(this.$refs.button, {
            text: () => this.text,
        });
    },

    beforeDestroy () {
        this.clipboard.destroy();
    },
};
</script>
