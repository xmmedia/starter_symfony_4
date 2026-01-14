<template>
    <div v-if="!selectOnly" class="field-wrap">
        <slot :id="id" name="label">
            <label :for="id" :class="labelClasses"><slot></slot></label>
        </slot>

        <FieldError v-if="v" :v="v">
            <template #required><slot name="required"></slot></template>
        </FieldError>

        <FieldSelectElement :id="id"
                            ref="select"
                            v-model="value"
                            :values="values"
                            :autofocus="autofocus"
                            :disabled="disabled"
                            :inert="inert"
                            :required="required"
                            :hide-default-option="hideDefaultOption"
                            :select-one-disabled="selectOneDisabled"
                            :select-classes="selectClasses">
            <template #default-option><slot name="default-option"></slot></template>
        </FieldSelectElement>

        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>
    </div>

    <FieldSelectElement v-else
                        :id="id"
                        ref="select"
                        v-model="value"
                        :values="values"
                        :autofocus="autofocus"
                        :disabled="disabled"
                        :inert="inert"
                        :required="required"
                        :hide-default-option="hideDefaultOption"
                        :select-one-disabled="selectOneDisabled"
                        :select-classes="selectClasses">
        <template #default-option><slot name="default-option"></slot></template>
    </FieldSelectElement>
</template>

<script setup>
import { useTemplateRef } from 'vue';
import cuid from 'cuid';
import FieldSelectElement from './field_select_element.vue';

const value = defineModel({ type: [ String, Number ] });
const field = useTemplateRef('select');
defineExpose({ field });

defineProps({
    /**
     * Either:
     * [{ value: '', label: '' }, ...]
     * or
     * { value: name, ... }
     * or
     * [ value, value, ... ]
     * The first is used by the component. The second and third are converted to the first.
     */
    values: {
        type: [Array, Object],
        required: true,
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
    selectOnly: {
        type: Boolean,
        default: false,
    },
    labelClasses: {
        type: [ String, Array, Object ],
        default: null,
    },
    selectClasses: {
        type: [ String, Array, Object ],
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    inert: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    hideDefaultOption: {
        type: Boolean,
        default: false,
    },
    selectOneDisabled: {
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
</script>
