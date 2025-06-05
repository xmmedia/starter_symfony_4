<template>
    <fieldset class="field-wrap-radios">
        <legend v-if="!!$slots.default"><slot></slot></legend>

        <FieldError v-if="v" :v="v">
            <template #required><slot name="required"></slot></template>
        </FieldError>

        <div :class="inputWrapperClasses">
            <div v-for="value in valuesCollection"
                 :key="value.value"
                 :class="{ 'field-wrap-radio' : !pills, 'radio-pill' : pills, 'radio-pill-dark': darkPills }">
                <input :id="id+'-'+value.value"
                       v-model="checked"
                       type="radio"
                       :name="'radios-'+id"
                       :checked="value.value === modelValue"
                       :value="value.value">
                <label v-if="!htmlLabel" :for="id+'-'+value.value">{{ value.label }}</label>
                <!-- eslint-disable-next-line vue/no-v-html -->
                <label v-else :for="id+'-'+value.value" v-html="value.label" />
            </div>
        </div>

        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>
    </fieldset>
</template>

<script setup>
import cuid from 'cuid';
import { computed } from 'vue';

const checked = defineModel({ type: [ String, Boolean ]});

const props = defineProps({
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
    row: {
        type: Boolean,
        default: true,
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
    pills: {
        type: Boolean,
        default: false,
    },
    htmlLabel: {
        type: Boolean,
        default: false,
    },
    darkPills: {
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

const inputWrapperClasses = computed(() => {
    if (props.pills) {
        return 'flex gap-2 flex-wrap';
    }

    if (props.row) {
        return 'flex flex-col xs:flex-row xs:flex-wrap';
    }

    return undefined;
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
</script>
