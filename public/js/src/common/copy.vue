<template>
    <button ref="button"
            :title="title"
            type="button">
        <svg class="w-4 h-4 fill-current">
            <use :xlink:href="iconsPath+'#copy'"></use>
        </svg>
    </button>
</template>

<script>
import ClipboardJs from 'clipboard';
import iconsPath from '@/../../images/icons-admin.svg';

export default {
    props: {
        text: {
            type: [String, Number],
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
            iconsPath,
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
