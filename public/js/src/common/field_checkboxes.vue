<template>
    <fieldset class="field-wrap-checkboxes">
        <legend><slot></slot></legend>

        <FieldError v-if="v" :v="v">
            <template #required><slot name="required"></slot></template>
        </FieldError>

        <div v-for="value in valuesCollection"
             :key="value.value"
             class="field-wrap-checkbox">
            <input :id="id+'-'+value.value"
                   type="checkbox"
                   :name="'checkboxes-'+id"
                   :checked="checked.includes(value.value)"
                   :value="value.value"
                   @input="inputChecked">
            <label :for="id+'-'+value.value">{{ value.label }}</label>
        </div>

        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>
    </fieldset>
</template>

<script setup>
import cuid from 'cuid';
import { computed } from 'vue';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: {
        type: Array,
        default: null,
    },
    /**
     * Either:
     * [{ value: '', label: '' }, ...]
     * or
     * { value: name, ... }
     * The first is used by the component. The second is converted to the first.
     */
    values: {
        type: [ Array, Object ],
        required: true,
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

const checked = computed({
    get () {
        return props.modelValue;
    },
    set (value) {
        emit('update:modelValue', value);
    },
});

const valuesCollection = computed(() => {
    if (Array.isArray(props.values)) {
        return props.values;
    }

    return Object.keys(props.values).map((value) => {
        return {
            value,
            label: props.values[value],
        };
    });
});

const inputChecked = (e) => {
    if (e.target.checked) {
        checked.value.push(e.target.value);
    } else {
        checked.value = checked.value.filter((v) => v !== e.target.value);
    }
}
</script>
