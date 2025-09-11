<template>
    <div class="field-wrap">
        <label :for="id">Role</label>

        <FieldError v-if="v" :v="v">
            <template #required>A Role is required.</template>
        </FieldError>

        <select :id="id" v-model="role">
            <option v-for="(name,_role) in rootStore.availableRoles" :key="_role" :value="_role">{{ name }}</option>
        </select>
    </div>
</template>

<script setup>
import cuid from 'cuid';
import { useRootStore } from '@/admin/stores/root';

const rootStore = useRootStore();

const role = defineModel({ type: String });

defineProps({
    v: {
        type: Object,
        required: true,
    },
    id: {
        type: String,
        default: () => cuid(),
    },
});
</script>
