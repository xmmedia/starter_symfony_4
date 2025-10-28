<template>
    <FieldEmail :model-value="user.email"
                :v="v.email"
                autocomplete="off"
                autofocus
                @update:model-value="setEmailDebounce" />

    <FieldPassword v-model="user.password"
                   v-model:set-password="user.setPassword"
                   :v="v.password"
                   :user-data="userDataForPassword"
                   :checkbox-label="editing ? 'Change password' : 'Set password'"
                   autocomplete="off" />

    <FieldCheckbox v-if="!editing" v-model="user.active">Active</FieldCheckbox>

    <FieldInput v-model="user.firstName" :v="v.firstName" autocomplete="off">First name</FieldInput>
    <FieldInput v-model="user.lastName" :v="v.lastName" autocomplete="off">Last name</FieldInput>

    <FieldRole v-model="user.role" :v="v.role" />

    <FieldCheckbox v-if="!user.setPassword && user.active" v-model="user.sendInvite">
        Send invite
        <template #help>
            The user will need to follow the link in the invite email
            before their account will be fully activated.
        </template>
    </FieldCheckbox>

    <FieldInput v-model="user.phoneNumber" type="tel" :v="v.phoneNumber">Phone number</FieldInput>
</template>

<script setup>
import FieldEmail from '@/common/field_email.vue';
import FieldPassword from './field_password.vue';
import FieldCheckbox from '@/common/field_checkbox.vue';
import FieldInput from '@/common/field_input.vue';
import FieldRole from './field_role.vue';
import debounce from 'lodash/debounce';
import { computed } from 'vue';

const user = defineModel({ type: Object });

defineProps({
    editing: {
        type: Boolean,
        default: false,
    },
    v: {
        type: Object,
        required: true,
    },
});

const userDataForPassword = computed(() => [
    user.value.email,
    user.value.firstName,
    user.value.lastName,
]);

const setEmailDebounce = debounce(function (email) {
    setEmail(email);
}, 100, { leading: true });
const setEmail = (value) => {
    user.value.email = value;
};
</script>
