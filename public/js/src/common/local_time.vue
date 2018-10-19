<template>
    <time :datetime="datetime">{{ displayTime }}</time>
</template>

<script>
export default {
    props: {
        datetime: {
            type: String,
            required: true
        },
        locale: {
            type: String,
            default: 'en-ca',
        },
    },

    computed: {
        displayTime () {
            const str = new Date(this.datetime)
                .toLocaleString('en-CA', { hour12: false });

            return str.substring(0, 10)+' '+str.substring(12, 17);
        },
    },
}
</script>
