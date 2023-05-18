<template>
    <fieldset class="field-wrap-radios">
        <legend><slot></slot></legend>

        <FieldError v-if="v" :v="v">
            <template #required><slot name="required"></slot></template>
        </FieldError>

        <div :class="{ 'flex flex-col xs:flex-row xs:flex-wrap' : row }">
            <div v-for="value in valuesCollection" :key="value.value" class="field-wrap-radio">
                <input :id="id+'-'+value.value"
                       type="radio"
                       :name="'organization-type-'+id"
                       :checked="value.value === modelValue"
                       :value="value.value"
                       @input="$emit('update:modelValue', $event.target.value)">
                <label :for="id+'-'+value.value">{{ value.name }}</label>
            </div>
        </div>

        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>
    </fieldset>
</template>

<script setup>
import cuid from 'cuid';
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: null,
    },
    /**
     * Either:
     * [{ value: '', name: ''}, ...]
     * or
     * { value: name, ... }
     * The first is used by the component. The second is converted to the first.
     */
    values: {
        type: [ Array, Object ],
        required: true,
    },
    row: {
        type: Boolean,
        default: true,
    },
    autofocus: {
        type: Boolean,
        default: false,
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
        return props.values;
    }

    return Object.keys(props.values).map((value) => {
        return {
            value,
            name: props.values[value],
        };
    });
});
</script>
