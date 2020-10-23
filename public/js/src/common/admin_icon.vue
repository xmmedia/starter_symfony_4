<template>
    <svg><use :xlink:href="iconsPath+'#'+icon"></use></svg>
</template>

<script>
import iconsPath from '@/../../images/icons-admin.svg';

export default {
    props: {
        icon: {
            type: String,
            required: true,
        },
    },

    data () {
        return {
            iconsPath,
        };
    },
};
</script>
