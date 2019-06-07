<template>
    <span>
        <button ref="link"
                class="button-link form-action"
                type="button"
                @click="open">Delete</button>

        <portal to="modal">
            <modal :adaptive="true"
                   :scrollable="true"
                   name="admin-delete"
                   height="auto"
                   transition="md"
                   @opened="opened">
                <div slot="top-right" class="text-4xl pr-6">
                    <button class="hover:no-underline text-white"
                            type="button"
                            @click="close">×</button>
                </div>

                <div v-if="!deleting" class="p-4 text-center">
                    <div class="my-4">
                        Are you sure you want to delete this {{ recordDesc }}?
                        This cannot be undone.
                    </div>
                    <div class="mb-4">
                        <button class="button bg-red-600 border-red-600 hover:bg-red hover:border-red-500"
                                type="button"
                                @click="deleteRecord">Delete</button>
                        <button ref="cancel"
                                class="form-action button-link"
                                type="button"
                                @click="close">Cancel</button>
                    </div>
                </div>

                <loading-spinner v-else class="p-8 text-center">Deleting {{ recordDesc }}...</loading-spinner>
            </modal>
        </portal>
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
                deleting: false,
            };
        },

        methods: {
            open () {
                this.$modal.show('admin-delete');
            },
            opened () {
                this.$refs.cancel.focus();
            },
            deleteRecord () {
                this.deleting = true;
                this.$emit('delete');
            },
            close () {
                this.$modal.hide('admin-delete');
                this.$refs.link.focus();
            },
        },
    }
</script>
