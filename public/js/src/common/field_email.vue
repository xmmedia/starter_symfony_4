<template>
    <div class="field-wrap">
        <label :for="id"><slot>Email (Username)</slot></label>

        <FieldError v-if="v.$error && v.$invalid">
            <template v-if="v.required.$invalid">
                An email is required.
            </template>
            <template v-else-if="v.email.$invalid">
                This email is invalid.
            </template>
            <template v-else-if="v.unique.$invalid">
                This email has already been used.
            </template>
        </FieldError>

        <input :id="id"
               ref="input"
               :value="modelValue"
               :autofocus="autofocus"
               :autocomplete="autocomplete"
               type="email"
               maxlength="150"
               @blur="checkEmail($event)"
               @input="$emit('update:modelValue', $event.target.value)">

        <div v-if="suggestedEmail" class="p-2 bg-emerald-900/70 text-white">
            Did you mean
            <button ref="suggestedEmailButton"
                    type="button"
                    class="button-link underline text-white mx-2 hover:text-gray-200
                           focus:text-white focus:ring-offset-emerald-900/10 focus:ring-offset-2"
                    @click="useSuggested">{{ suggestedEmail }}</button>?
        </div>

        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>
    </div>
</template>

<script setup>
import { nextTick, ref } from 'vue';
import cuid from 'cuid';
import mailcheck from 'mailcheck';

defineProps({
    modelValue: {
        type: String,
        default: null,
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
    autocomplete: {
        type: String,
        default: null,
    },
    v: {
        type: Object,
        required: true,
    },
    id: {
        type: String,
        default: function () {
            return cuid();
        },
    },
});

const emit = defineEmits(['update:modelValue']);

const suggestedEmail = ref(null);
const suggestedEmailButton = ref();
const input = ref();

function checkEmail (event) {
    if (!event.target.value) {
        return;
    }

    mailcheck.run({
        email: event.target.value,
        suggested: (suggestion) => {
            suggestedEmail.value = suggestion.full;

            nextTick(() => {
                suggestedEmailButton.value.focus();
            });
        },
        empty: () => {
            suggestedEmail.value = null;
        },
    });
}
function useSuggested () {
    emit('update:modelValue', suggestedEmail.value);
    suggestedEmail.value = null;
    input.value.focus();
}
</script>
