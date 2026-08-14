<template>
    <form class="form-wrap" method="post" @submit.prevent>
        <div class="flex gap-8">
            <FieldRadios v-model="filters.queueName" :values="queueNameOptions" :pills="true">
                Queue
            </FieldRadios>
        </div>
        <ul class="form-extra_actions">
            <li>
                <button type="button" class="form-action button-link" @click="$emit('reset')">Reset</button>
            </li>
        </ul>
    </form>
</template>

<script setup>
import FieldRadios from '@/common/field_radios.vue';

defineEmits(['reset']);

const filters = defineModel({ type: Object, required: true });

const queueNameOptions = [
    { value: 'ALL', label: 'All' },
    { value: 'default', label: 'Queued' },
    { value: 'failed', label: 'Failed' },
];
</script>
