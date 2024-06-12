<template>
    <div class="field-wrap">
        <slot :id="id" name="label">
            <label :for="id"><slot>Email (Username)</slot></label>
        </slot>

        <FieldError v-if="v && v.$error && v.$invalid">
            <template v-if="v.required.$invalid">
                <slot name="required">An email is required.</slot>
            </template>
            <template v-else-if="v.email.$invalid">
                <slot name="invalid">This email is invalid.</slot>
            </template>
            <template v-else-if="v.unique.$invalid">
                <slot name="unique">This email has already been used.</slot>
            </template>
        </FieldError>

        <input :id="id"
               ref="input"
               v-focus="autofocus"
               :value="modelValue"
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
import emailSpellChecker from '@zootools/email-spell-checker';

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
        default: null,
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

    const result = emailSpellChecker.run({
        email: event.target.value,
    });

    if (result) {
        suggestedEmail.value = result.full;
        nextTick(() => {
            suggestedEmailButton.value.focus();
        });
    } else {
        suggestedEmail.value = null;
    }
}
function useSuggested () {
    emit('update:modelValue', suggestedEmail.value);
    suggestedEmail.value = null;
    input.value.focus();
}
</script>
