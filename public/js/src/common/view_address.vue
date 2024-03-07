<template>
    <address>
        <div>
            {{ address.line1 }}
            <Copy :text="string" :icon-component="iconComponent" title="Copy address" />
        </div>
        <div v-if="address.line2">{{ address.line2 }}</div>
        <div>{{ address.city }}, {{ address.province.abbreviation }}
            &MediumSpace;{{ address.postalCode.replace(' ', '&nbsp;') }}</div>
    </address>
</template>

<script setup>
import { computed } from 'vue';
import Copy from '@/common/copy.vue';

const props = defineProps({
    address: {
        type: Object,
        required: true,
    },
    iconComponent: {
        type: String,
        default: 'AdminIcon',
    },
});

const string = computed(() => {
    let str = props.address.line1 + "\n";
    if (props.address.line2) {
        str += ', ' + props.address.line2 + "\n";
    }
    str += props.address.city + ', ' + props.address.province.abbreviation + '  ' + props.address.postalCode + "\n" + props.address.country.name;

    return str;
});
</script>
