<template>
    <select :id="id"
            ref="select"
            v-focus="autofocus"
            :disabled="disabled"
            :inert="inert"
            :required="required"
            @change="value = $event.target.value">
        <option value="" :selected="null === value" :disabled="selectOneDisabled">
            <slot name="default-option">– Select one –</slot>
        </option>
        <option v-for="_value in valuesCollection"
                :key="_value.value"
                :value="_value.value"
                :selected="_value.value === value">{{ _value.label }}</option>
    </select>
</template>

<script setup>
import { computed, useTemplateRef } from 'vue';

const value = defineModel({ type: [ String, Number ] });
const field = useTemplateRef('select');
defineExpose({ field });

const props = defineProps({
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
    selectOneDisabled: {
        type: Boolean,
        default: true,
    },
    id: {
        type: String,
        default: null,
    },
});

const valuesCollection = computed(() => {
    if (Array.isArray(props.values)) {
        if (typeof props.values[0] === 'string' || typeof props.values[0] === 'number') {
            return props.values.map((value) => {
                return {
                    value,
                    label: value,
                };
            });
        }

        return props.values;
    }

    return Object.keys(props.values).map((value) => {
        return {
            value,
            label: props.values[value],
        };
    });
});
</script>
