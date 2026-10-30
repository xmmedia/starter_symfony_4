<template>
    <div class="field-wrap">
        <label :for="id">Role</label>

        <FieldError v-if="v" :v="v">
            <template #required>A Role is required.</template>
        </FieldError>

        <select :id="id" v-model="role">
            <option v-for="(name,_role) in availableRoles" :key="_role" :value="_role">{{ name }}</option>
        </select>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { createId } from '@paralleldrive/cuid2';
import { useRootStore } from '@/admin/stores/root';

const rootStore = useRootStore();

const role = defineModel({ type: String, default: null });

// only super admins can make someone a super admin, but anyone can keep a super admin's role
const availableRoles = computed(() => {
    if (rootStore.hasRole('ROLE_SUPER_ADMIN') || 'ROLE_SUPER_ADMIN' === role.value) {
        return rootStore.availableRoles;
    }

    // eslint-disable-next-line no-unused-vars
    const { ROLE_SUPER_ADMIN, ...roles } = rootStore.availableRoles;

    return roles;
});

defineProps({
    v: {
        type: Object,
        required: true,
    },
    id: {
        type: String,
        default: () => createId(),
    },
});
</script>
