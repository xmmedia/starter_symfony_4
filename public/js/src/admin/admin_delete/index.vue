<template>
    <span>
        <button ref="link"
                class="button-link form-action"
                type="button"
                @click="show = true">Delete</button>

        <admin-modal v-if="show" @closed="show = false" @opened="opened">
            <div v-if="!deleting" class="text-center">
                <div class="my-4">
                    Are you sure you want to delete this {{ recordDesc }}?
                    This cannot be undone.
                </div>
                <div class="mt-8">
                    <button class="button button-critical bg-red-600 text-white"
                            type="button"
                            @click="deleteRecord">Delete</button>
                    <button ref="cancel"
                            class="form-action button-link"
                            type="button"
                            @click="close">Cancel</button>
                </div>
            </div>

            <loading-spinner v-else class="p-12 text-center">
                Deleting {{ recordDesc }}…
            </loading-spinner>
        </admin-modal>
    </span>
</template>

<script>
export default {
    props: {
        recordDesc: {
            type: String,
            required: true,
        },
    },

    data () {
        return {
            show: false,
            deleting: false,
        };
    },

    methods: {
        opened () {
            this.$refs.cancel.focus();
        },
        deleteRecord () {
            this.deleting = true;
            this.$emit('delete');
        },
        close () {
            this.show = false;
            this.deleting = false;
            this.$refs.link.focus();
        },
    },
}
</script>
