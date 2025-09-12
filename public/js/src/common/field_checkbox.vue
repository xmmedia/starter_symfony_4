<template>
    <div class="field-wrap">
        <div class="field-wrap-checkbox">
            <FieldError v-if="v" :v="v">
                <template #required><slot name="required"></slot></template>
            </FieldError>

            <input :id="id" v-model="checked" type="checkbox" :value="true" :disabled="disabled">
            <label :for="id" :class="labelClasses"><slot></slot></label>
        </div>

        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>
    </div>
</template>

<script setup>
import cuid from 'cuid';

const checked = defineModel({ type: Boolean });

defineProps({
    disabled: {
        type: Boolean,
        default: false,
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
</script>
