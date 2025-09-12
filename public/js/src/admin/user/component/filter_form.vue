<template>
    <form class="form-wrap w-full max-w-5xl" method="post" @submit.prevent>
        <div class="flex flex-col md:flex-row gap-x-8">
            <FieldInput v-model="filters.q" class="grow">Search</FieldInput>
            <FieldRadios v-model="filters.role" :values="roles" :pills="true">Role</FieldRadios>
            <FieldRadios v-model="filters.accountStatus"
                         :values="accountStatuses"
                         :pills="true">Account Status</FieldRadios>
        </div>

        <ul class="form-extra_actions">
            <li>
                <button type="button" class="form-action button-link" @click="$emit('reset')">Reset</button>
            </li>
        </ul>
    </form>
</template>

<script setup>
import { computed } from 'vue';
import debounce from 'lodash/debounce';
import FieldInput from '@/common/field_input.vue';
import FieldRadios from '@/common/field_radios.vue';

const emit = defineEmits(['update:modelValue', 'reset']);

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
});

const filters = computed({
    get () {
        return props.modelValue;
    },
    set (value) {
        // debounce so typing in the search box doesn't cause too many updates
        debounce(() => {
            emit('update:modelValue', value);
        }, 1000);
    },
});

const roles = [
    { value: 'ALL', label: 'All' },
    { value: 'ADMIN', label: 'Admin' },
    { value: 'USER', label: 'User' },
];

const accountStatuses = [
    { value: 'ALL', label: 'All' },
    { value: 'ACTIVE', label: 'Active' },
    { value: 'INACTIVE', label: 'Inactive' },
];
</script>
