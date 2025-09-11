<template>
    <div class="field-wrap">
        <slot :id="id" name="label">
            <label :for="id" :class="labelClasses"><slot></slot></label>
        </slot>

        <FieldError v-if="v" :v="v">
            <template #required><slot name="required"></slot></template>
            <template #minLength><slot name="minLength"></slot></template>
            <template #maxLength><slot name="maxLength"></slot></template>
            <template #between><slot name="between"></slot></template>
            <template #minValue><slot name="minValue"></slot></template>
            <template #maxValue><slot name="maxValue"></slot></template>
            <template #numeric><slot name="numeric"></slot></template>
            <template #decimal><slot name="decimal"></slot></template>
            <template #url><slot name="url"></slot></template>
            <template #email><slot name="email"></slot></template>
            <template #valid><slot name="valid"></slot></template>
        </FieldError>

        <div :class="inputWrapperClasses">
            <slot name="prefix"></slot>
            <input :id="id"
                   ref="input"
                   v-focus="autofocus"
                   :value="modelValue"
                   :type="type"
                   :maxlength="maxLength"
                   :autocomplete="autocomplete"
                   :placeholder="placeholder"
                   :readonly="readonly"
                   :disabled="disabled"
                   :min="min"
                   :max="max"
                   :step="step"
                   :class="inputClasses"
                   @input="$emit('update:modelValue', $event.target.value)"
                   @focus="$emit('focus')"
                   @blur="$emit('blur')">
            <slot name="suffix"></slot>
        </div>

        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>
    </div>
</template>

<script setup>
import cuid from 'cuid';
import has from 'lodash/has';
import { ref } from 'vue';

defineEmits([ 'update:modelValue', 'focus', 'blur' ]);

const props = defineProps({
    modelValue: {
        type: [ String, Number ],
        default: null,
    },
    type: {
        type: String,
        default: 'text',
    },
    autocomplete: {
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
    readonly: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    min: {
        type: [ Number, String ],
        default: null,
    },
    max: {
        type: [ Number, String ],
        default: null,
    },
    step: {
        type: [ Number, String ],
        default: null,
    },
    inputClasses: {
        type: [ String, Array, Object ],
        default: null,
    },
    inputWrapperClasses: {
        type: [ String, Array, Object ],
        default: 'flex items-center gap-x-2',
    },
    labelClasses: {
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

const input = ref();
const maxLength = has(props.v, 'maxLength') ? props.v.maxLength.$params.max : null;

defineExpose({ input });
</script>
