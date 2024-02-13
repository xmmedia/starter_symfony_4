<template>
    <!-- !! Note: !! -->
    <!-- Using this slot and replacing the whole button will result in the refocusing of the button not working -->
    <slot name="button" :open="open">
        <button ref="link"
                :disabled="disabled"
                :class="linkClasses"
                :title="buttonTitle"
                type="button"
                @click="open">Delete</button>
    </slot>

    <AdminModal v-if="show" @closed="closed" @opened="opened">
        <div v-if="!deleting" class="text-center">
            <div class="my-4">
                <slot>
                    Are you sure you want to delete this {{ recordDesc }}?
                    <br>This cannot be undone.
                </slot>
            </div>
            <slot name="additional" />
            <div class="mt-8">
                <button class="button button-critical bg-red-600 text-white focus:ring-offset-red-800"
                        type="button"
                        @click="deleteRecord">Delete</button>
                <button ref="cancel"
                        class="form-action button-link text-slate-300
                               focus:ring-offset-4 ring-offset-gray-800 focus:text-slate-400 hover:text-slate-400"
                        type="button"
                        @click="close">Cancel</button>
            </div>
        </div>

        <LoadingSpinner v-else class="p-12 text-center">
            Deleting {{ recordDesc }}…
        </LoadingSpinner>
    </AdminModal>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
    recordDesc: {
        type: String,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    linkClasses: {
        type: String,
        default: 'button-link form-action',
    },
    buttonTitle: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['delete']);

const show = ref(false);
const deleting = ref(false);

const cancel = ref(null);
const link = ref(null);

function open () {
    show.value = true;
}

function opened () {
    if (!deleting.value) {
        cancel.value.focus();
    }
}

function closed ()  {
    show.value = false;
    // reset everything else
    deleting.value = false;
    cancel.value = null;
    if (link.value) {
        link.value.focus();
    }
}

function deleteRecord () {
    deleting.value = true;
    emit('delete');
}

function close () {
    show.value = false;
    deleting.value = false;
    if (link.value) {
        link.value.focus();
    }
}
</script>
