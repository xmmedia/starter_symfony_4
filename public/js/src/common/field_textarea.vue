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
                  ref="input"
                  v-focus="autofocus"
                  :value="modelValue"
                  :maxlength="maxLength"
                  :placeholder="placeholder"
                  :class="inputClasses"
                  @input="$emit('update:modelValue', $event.target.value)" />

        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>
    </div>
</template>

<script setup>
import cuid from 'cuid';
import has from 'lodash/has';

defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: {
        type: String,
        default: null,
    },
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
        default: function () {
            return cuid();
        },
    },
});

const maxLength = has(props.v, 'maxLength') ? props.v.maxLength.$params.max : null;
</script>
