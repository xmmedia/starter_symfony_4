<template>
    <span>
        <div>
            <a href="" @click.prevent="$modal.show('admin-delete');" class="form-action">Delete</a>
        </div>

        <modal name="admin-delete"
               height="auto"
               :adaptive="true"
               :scrollable="true"
               transition="md">
            <div slot="top-right" class="text-4xl pr-6">
                <a href="" @click.prevent="close" class="hover:no-underline text-white">×</a>
            </div>

            <div class="p-4 text-center">
                <p>Are you sure you want to delete this {{ recordDesc }}? This cannot be undone.</p>
                <div>
                    <form name="form" method="post" v-bind:action="action">
                        <input type="hidden" name="_method" value="DELETE">
                        <button class="bg-red-dark border-red-dark hover:bg-red hover:border-red">Delete</button>
                        <input type="hidden" id="form__token" name="form[_token]" v-bind:value="csrfToken">
                    </form>
                </div>
            </div>
        </modal>
    </span>
</template>

<script>
export default {
    props: {
        action: {
            type: String,
            required: true
        },
        recordDesc: {
            type: String,
            required: true
        },
        csrfToken: {
            type: String,
            required: true
        },
    },
}
</script>
