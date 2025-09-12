<template>
    <div class="field-wrap">
        <slot :id="id" name="label">
            <label :for="id"><slot></slot></label>
        </slot>

        <FieldError v-if="v" :v="v">
            <template #required><slot name="required"></slot></template>
            <template #minLength><slot name="minLength"></slot></template>
            <template #maxLength><slot name="maxLength"></slot></template>
            <template #valid><slot name="valid"></slot></template>
        </FieldError>

        <textarea :id="id"
                  v-model="textArea"
                  ref="input"
                  v-focus="autofocus"
                  :value="modelValue"
                  :maxlength="maxLength"
                  :placeholder="placeholder"
                  :class="inputClasses" />

        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>
    </div>
</template>

<script setup>
import cuid from 'cuid';
import has from 'lodash/has';

const textArea = defineModel({ type: String });

const props = defineProps({
    placeholder: {
        type: String,
        default: null,
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
    inputClasses: {
        type: [ String, Array, Object ],
        default: null,
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

const maxLength = has(props.v, 'maxLength') ? props.v.maxLength.$params.max : null;
</script>
