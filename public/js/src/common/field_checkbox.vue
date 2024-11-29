<template>
    <div class="field-wrap">
        <div class="field-wrap-checkbox">
            <FieldError v-if="v" :v="v">
                <template #required><slot name="required"></slot></template>
            </FieldError>

            <input :id="id" v-model="checked" type="checkbox" :value="true" :disabled="disabled">
            <label :for="id"><slot></slot></label>
        </div>

        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>
    </div>
</template>

<script setup>
import cuid from 'cuid';
import { computed } from 'vue';

const emit = defineEmits([ 'update:modelValue' ]);

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: null,
    },
    disabled: {
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

const checked = computed({
    get () {
        return props.modelValue;
    },
    set (value) {
        emit('update:modelValue', value);
    },
});
</script>
