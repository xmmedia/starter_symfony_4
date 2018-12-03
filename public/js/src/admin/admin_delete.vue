<template>
    <span>
        <div>
            <a href="" class="form-action" @click.prevent="$modal.show('admin-delete');">Delete</a>
        </div>

        <portal to="modal">
            <modal :adaptive="true"
                   :scrollable="true"
                   name="admin-delete"
                   height="auto"
                   transition="md">
                <div slot="top-right" class="text-4xl pr-6">
                    <a href="" class="hover:no-underline text-white" @click.prevent="close">×</a>
                </div>

                <div class="p-4 text-center">
                    <p>Are you sure you want to delete this {{ recordDesc }}? This cannot be undone.</p>
                    <div>
                        <form :action="action" name="form" method="post">
                            <input type="hidden" name="_method" value="DELETE">
                            <button class="button bg-red-dark border-red-dark hover:bg-red hover:border-red">
                                Delete
                            </button>
                            <input id="form__token" :value="csrfToken" type="hidden" name="form[_token]">
                        </form>
                    </div>
                </div>
            </modal>
        </portal>
    </span>
</template>

<script>
export default {
    props: {
        action: {
            type: String,
            required: true,
        },
        recordDesc: {
            type: String,
            required: true,
        },
        csrfToken: {
            type: String,
            required: true,
        },
    },
}
</script>
