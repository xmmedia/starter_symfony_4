<template>
    <span class="flex justify-between items-center w-full py-2 pr-2 pl-4 mb-1 font-light">
        <span class="flex flex-wrap justify-start items-center w-full lg:text-base">
            <svg class="w-5 h-5 mr-3 mb-0" width="20" height="20">
                <use :xlink:href="iconsPath+'#'+icon"></use>
            </svg>
            <span class="leading-none">{{ label }}</span>
        </span>
        <svg v-if="hasSubnav"
             class="w-3 h-3"
             width="12"
             height="12">
            <use :xlink:href="iconsPath+'#gt'"></use>
        </svg>
    </span>
</template>

<script>
import iconsPath from '@/../../images/icons-admin.svg';

export default {
    props: {
        icon: {
            type: String,
            required: true,
        },
        label: {
            type: String,
            required: true,
        },
        hasSubnav: {
            type: Boolean,
            default: false,
        },
    },

    data () {
        return {
            iconsPath,
        };
    },
}
</script>
