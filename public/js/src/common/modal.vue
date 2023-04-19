<template>
    <vue-final-modal v-model="show"
                     class="flex justify-center items-center"
                     content-class="flex flex-col max-w-xl mx-4 p-4 text-slate-300 bg-gray-800
                                    border border-gray-700 rounded-lg space-y-2"
                     overlay-transition="vfm-fade"
                     content-transition="vfm-fade"
                     @before-open="$emit('before-open', $event)"
                     @opened="$emit('opened')"
                     @before-close="$emit('before-close', $event)"
                     @closed="$emit('closed')">
        <div class="relative">
            <div class="absolute top-0 right-0 text-4xl leading-3">
                <button class="text-slate-600 hover:text-slate-300 transition-colors duration-300"
                        type="button"
                        @click="close">×</button>
            </div>

            <div class="p-6">
                <slot :close="close"></slot>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
import { VueFinalModal as vueFinalModal } from 'vue-final-modal';

export default {
    components: {
        vueFinalModal,
    },

    data () {
        return {
            show: false,
        };
    },

    mounted () {
        this.$nextTick(() => {
            this.show = true;
        });
    },

    beforeUnmount () {
        this.close();
    },

    methods: {
        close () {
            this.show = false;
        },
    },
}
</script>
