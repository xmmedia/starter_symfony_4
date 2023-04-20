<template>
    <span>
        <button ref="link"
                class="button-link form-action"
                type="button"
                @click="show = true">Delete</button>

        <AdminModal v-if="show" @closed="show = false" @opened="opened">
            <div v-if="!deleting" class="text-center">
                <div class="my-4">
                    Are you sure you want to delete this {{ recordDesc }}?
                    <br>This cannot be undone.
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
    </span>
</template>

<script setup>
import { defineEmits, ref } from 'vue';

defineProps({
    recordDesc: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['delete']);

const show = ref(false);
const deleting = ref(false);

const cancel = ref(null);
const link = ref(null);

function opened () {
    if (!deleting.value) {
        cancel.value.focus();
    }
}

function deleteRecord () {
    deleting.value = true;
    emit('delete');
}

function close () {
    show.value = false;
    deleting.value = false;
    link.value.focus();
}
</script>
