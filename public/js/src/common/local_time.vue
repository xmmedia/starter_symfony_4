<template>
    <time :datetime="datetime">
        <span class="whitespace-nowrap">{{ displayDate }}</span>
        <span class="whitespace-nowrap">{{ displayTime }}</span>
    </time>
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
            default: 'en-CA',
        },
    },

    computed: {
        localeString () {
            return new Date(this.datetime).toLocaleString(this.locale, { hour12: false });
        },

        displayDate () {
            return this.localeString.substring(0, 10);
        },
        displayTime () {
            return this.localeString.substring(12, 17);
        },
    },
}
</script>
