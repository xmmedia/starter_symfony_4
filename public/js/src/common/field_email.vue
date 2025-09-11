<template>
    <div class="field-wrap">
        <slot :id="id" name="label">
            <label :for="id"><slot>Email (Username)</slot></label>
        </slot>

        <FieldError v-if="v && v.$error && v.$invalid">
            <template v-if="v?.required?.$invalid">
                <slot name="required">An email is required.</slot>
            </template>
            <template v-else-if="v?.email?.$invalid">
                <slot name="invalid">This email is invalid.</slot>
            </template>
            <template v-else-if="v?.unique?.$invalid">
                <slot name="unique">This email has already been used.</slot>
            </template>
        </FieldError>

        <input :id="id"
               ref="input"
               v-focus="autofocus"
               :value="modelValue"
               :autocomplete="autocomplete"
               :placeholder="placeholder"
               type="email"
               maxlength="150"
               @blur="checkEmail($event)"
               @input="$emit('update:modelValue', $event.target.value)">

        <div v-if="suggestedEmail" class="p-2 bg-emerald-900/70 text-white">
            Did you mean
            <button type="button"
                    class="button-link underline text-white mx-2 hover:text-gray-200
                           focus:text-white focus:ring-offset-emerald-900/10 focus:ring-offset-2"
                    @click="useSuggested">{{ suggestedEmail }}</button>?
        </div>

        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import cuid from 'cuid';
import emailSpellChecker from '@zootools/email-spell-checker';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
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
    placeholder: {
        type: String,
        default: null,
    },
    checkEmailOnBlur: {
        type: Boolean,
        default: true,
    },
    v: {
        type: Object,
        default: null,
    },
    id: {
        type: String,
        default: () => cuid(),
    },
});

const suggestedEmail = ref(null);
const input = ref();

const checkEmail = (event) => {
    if (!props.checkEmailOnBlur || !event.target.value) {
        suggestedEmail.value = null;

        return;
    }

    const result = emailSpellChecker.run({
        email: event.target.value,
    });

    if (result) {
        suggestedEmail.value = result.full;
    } else {
        suggestedEmail.value = null;
    }
}
const useSuggested = () => {
    emit('update:modelValue', suggestedEmail.value);
    suggestedEmail.value = null;
    input.value.focus();
}
</script>
