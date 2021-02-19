<template>
    <portal to="modal">
        <vue-modal :key="name"
                   :adaptive="true"
                   :scrollable="true"
                   :name="name"
                   :height="height"
                   :width="width"
                   :draggable="draggable"
                   transition="md"
                   @before-open="$emit('before-open', $event)"
                   @opened="$emit('opened')"
                   @before-close="$emit('before-close', $event)"
                   @closed="$emit('closed')">
            <div class="float-right pr-4 pt-4 text-4xl leading-3">
                <button class="text-gray-300 hover:text-gray-700 transition-colors duration-300"
                        type="button"
                        @click="close">×</button>
            </div>

            <div class="p-8">
                <slot :close="close"></slot>
            </div>
        </vue-modal>
    </portal>
</template>

<script>
import cuid from 'cuid';

export default {
    props: {
        height: {
            type: [String, Number],
            default: 'auto',
        },
        width: {
            type: [String, Number],
            default: 600,
        },
        draggable: {
            type: Boolean,
            default: false,
        },
        name: {
            type: [String, Number],
            default () {
                return cuid();
            },
        },
    },

    mounted () {
        this.$nextTick(() => {
            this.$modal.show(this.name);
        });
    },

    beforeDestroy () {
        this.close();
    },

    methods: {
        close () {
            this.$modal.hide(this.name);
        },
    },
};
</script>
