<template>
    <!-- eslint-disable vue/no-v-html -->
    <time :datetime="datetime" v-html="displayTime"></time>
</template>

<script>
export default {
    props: {
        datetime: {
            type: [String, Date],
            required: true,
        },
        locale: {
            type: String,
            default: 'en-ca',
        },
    },

    computed: {
        displayTime () {
            const str = new Date(this.datetime).toLocaleString('en-CA', { hour12: false });

            // add classes so the date and time don't wrap
            return '<span class="whitespace-no-wrap">' + str.substring(0, 10) + '</span> <span class="whitespace-no-wrap">' + str.substring(12, 17) + '</span>';
        },
    },
}
</script>
