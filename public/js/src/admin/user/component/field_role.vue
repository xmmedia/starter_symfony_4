<template>
    <div class="field-wrap">
        <label :for="id">Role</label>

        <FieldError v-if="v.$error && v.$invalid">
            <template v-if="!v.required">
                A Role is required.
            </template>
        </FieldError>

        <select :id="id"
                :value="modelValue"
                @change="$emit('update:modelValue', $event.target.value)">
            <option v-for="(name,role) in rootStore.availableRoles"
                    :key="role"
                    :value="role">{{ name }}</option>
        </select>
    </div>
</template>

<script setup>
import cuid from 'cuid';
import { useRootStore } from '@/admin/stores/root';

const rootStore = useRootStore();

defineProps({
    modelValue: {
        type: String,
        default: null,
    },
    v: {
        type: Object,
        required: true,
    },
});

const id = cuid();
</script>
