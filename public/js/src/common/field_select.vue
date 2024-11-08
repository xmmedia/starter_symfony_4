<template>
    <div class="field-wrap">
        <slot :id="id" name="label">
            <label :for="id" :class="labelClasses"><slot></slot></label>
        </slot>

        <FieldError v-if="v" :v="v">
            <template #required><slot name="required"></slot></template>
        </FieldError>

        <select :id="id" v-model="value" v-focus="autofocus" :disabled="disabled">
            <option :value="null" :disabled="selectOneDisabled">– Select one –</option>
            <option v-for="_value in valuesCollection"
                    :key="_value.value"
                    :value="_value.value">{{ _value.label }}</option>
        </select>

        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>
    </div>
</template>

<script setup>
import cuid from 'cuid';
import { computed } from 'vue';

const value = defineModel({ type: [ String, Number ] });

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
    labelClasses: {
        type: [ String, Array, Object ],
        default: null,
    },
    disabled: {
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
        default: function () {
            return cuid();
        },
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
