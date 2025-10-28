<template>
    <div>
        <FieldCheckbox v-model="setPassword" :class="{ 'mb-2' : setPassword }">
            {{ checkboxLabel }}
        </FieldCheckbox>

        <FieldPassword v-show="setPassword"
                       v-model="password"
                       :v="v"
                       :user-data="userData"
                       :show-help="true"
                       :required="setPassword"
                       :autocomplete="autocomplete"
                       :data-1p-ignore="'off' === autocomplete"
                       icon-component="AdminIcon"
                       class="ml-6" />
    </div>
</template>

<script setup>
import cuid from 'cuid';
import FieldCheckbox from '@/common/field_checkbox.vue';
import FieldPassword from '@/common/field_password_with_errors.vue';

const password = defineModel({ type: String });
const setPassword = defineModel('setPassword', { type: Boolean });

defineProps({
    checkboxLabel: {
        type: String,
        default: 'Set Password',
    },
    autocomplete: {
        type: String,
        default: 'new-password',
    },
    v: {
        type: Object,
        required: true,
    },
    userData: {
        type: Array,
        default () {
            return [];
        },
    },
    id: {
        type: String,
        default: () => cuid(),
    },
});
</script>
