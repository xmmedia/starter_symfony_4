<template>
    <form class="form-wrap w-full max-w-5xl" method="post" @submit.prevent>
        <div class="flex gap-8">
            <div class="flex flex-col md:flex-row gap-x-8">
                <FieldSelect v-model="filters.dateRange"
                             :values="dateRangeOptions"
                             :hide-default-option="true">
                    Date
                    <template v-if="dateRangeLabel" #help>{{ dateRangeLabel }}</template>
                </FieldSelect>
                <div v-if="'CUSTOM' === filters.dateRange" class="field-wrap">
                    <label :for="dateRangeInputId">Custom Range</label>
                    <div class="flex items-center gap-x-2">
                        <input :id="dateRangeInputId"
                               ref="dateRangeInputRef"
                               type="text"
                               readonly
                               placeholder="Select range…"
                               class="w-56" />
                    </div>
                </div>
                <FieldInput v-model="filters.q" placeholder="Search by email…">
                    Email
                </FieldInput>
            </div>
            <FieldRadios v-model="filters.eventType" :values="filterValues" :pills="true">
                Type
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
import { computed, nextTick, onBeforeUnmount, onMounted, useTemplateRef, watch } from 'vue';
import Flatpickr from 'flatpickr';
import cuid from 'cuid';
import FieldInput from '@/common/field_input.vue';
import FieldRadios from '@/common/field_radios.vue';
import FieldSelect from '@/common/field_select.vue';
import debounce from 'lodash/debounce';

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

const filterValues = [
    { value: 'ALL',           label: 'All' },
    { value: 'login',         label: 'Successful' },
    { value: 'login_failed',  label: 'Failed' },
    { value: 'impersonation', label: 'Impersonation' },
];

const dateRangeOptions = [
    { value: 'ALL',        label: 'All Time' },
    { value: 'LAST_HOUR',  label: 'Last Hour' },
    { value: 'LAST_24H',   label: 'Last 24 Hours' },
    { value: 'LAST_WEEK',  label: 'Last Week' },
    { value: 'LAST_MONTH', label: 'Last Month' },
    { value: 'CUSTOM',     label: 'Custom…' },
];

const dateRangeInputRef = useTemplateRef('dateRangeInputRef');
const dateRangeInputId = cuid();
let flatpickrInstance = null;

const handleDateRangeChange = (selectedDates) => {
    if (2 === selectedDates.length) {
        filters.value.customDateFrom = Flatpickr.formatDate(selectedDates[0], 'Y-m-d');
        filters.value.customDateTo = Flatpickr.formatDate(selectedDates[1], 'Y-m-d');
    } else {
        filters.value.customDateFrom = null;
        filters.value.customDateTo = null;
    }
};

const initFlatpickr = () => {
    flatpickrInstance = Flatpickr(dateRangeInputRef.value, {
        mode: 'range',
        dateFormat: 'Y-m-d',
        defaultDate: [filters.value.customDateFrom, filters.value.customDateTo].filter(Boolean),
        onChange: handleDateRangeChange,
    });
};

const dateRangeLabel = computed(() => {
    const fmtDateTime = (d) => {
        const date = d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
        const time = d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', hour12: false });

        return `${date} at ${time}`;
    };
    const fmtDate = (d) => d.toLocaleDateString(undefined, {
        month: 'short', day: 'numeric', year: 'numeric',
    });
    const now = new Date();

    switch (filters.value.dateRange) {
        case 'LAST_HOUR':
            return `${fmtDateTime(new Date(Date.now() - 60 * 60 * 1000))} – ${fmtDateTime(now)}`;
        case 'LAST_24H':
            return `${fmtDateTime(new Date(Date.now() - 24 * 60 * 60 * 1000))} – ${fmtDateTime(now)}`;
        case 'LAST_WEEK':
            return `${fmtDateTime(new Date(Date.now() - 7 * 24 * 60 * 60 * 1000))} – ${fmtDateTime(now)}`;
        case 'LAST_MONTH':
            return `${fmtDateTime(new Date(Date.now() - 30 * 24 * 60 * 60 * 1000))} – ${fmtDateTime(now)}`;
        case 'CUSTOM':
            if (!filters.value.customDateFrom || !filters.value.customDateTo) return null;

            return `${fmtDate(new Date(filters.value.customDateFrom + 'T00:00:00'))} –
                ${fmtDate(new Date(filters.value.customDateTo + 'T00:00:00'))}`;
        default:
            return null;
    }
});

onMounted(() => {
    if ('CUSTOM' === filters.value.dateRange) {
        nextTick(initFlatpickr);
    }
});

onBeforeUnmount(() => {
    flatpickrInstance?.destroy();
});

watch(() => filters.value.dateRange, async (newValue) => {
    if ('CUSTOM' === newValue) {
        await nextTick();
        initFlatpickr();
    } else {
        flatpickrInstance?.destroy();
        flatpickrInstance = null;
        filters.value.customDateFrom = null;
        filters.value.customDateTo = null;
    }
});
</script>
