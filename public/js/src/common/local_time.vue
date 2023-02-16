<template>
    <time :datetime="datetime">
        <span class="whitespace-nowrap">{{ displayDate }}</span>
        <span class="whitespace-nowrap">{{ displayTime }}</span>
    </time>
</template>

<script>
import { format as formatDate } from 'date-fns';

export default {
    props: {
        datetime: {
            type: [String, Date],
            required: true,
        },
    },

    computed: {
        date () {
            // just in case it's a string
            return new Date(this.datetime);
        },

        displayDate () {
            return formatDate(this.date, 'yyyy-MM-dd');
        },
        displayTime () {
            return formatDate(this.date, 'HH:mm');
        },
    },
}
</script>
