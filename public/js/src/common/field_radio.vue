<template>
    <div class="field-wrap-radio">
        <FieldError v-if="v" :v="v">
            <template #required><slot name="required"></slot></template>
        </FieldError>

        <input :id="id" v-model="checked" type="radio" :name="name" :value="value">
        <label :for="id"><slot></slot></label>

        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>
    </div>
</template>

<script setup>
import cuid from 'cuid';
import { computed } from 'vue';

const checked = defineModel({ type: Boolean });

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    value: {
        type: [ String, Boolean, Number ],
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
