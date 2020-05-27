<template>
    <!-- eslint-disable-next-line vue/no-v-html -->
    <div v-if="svg" style="height: 0; width: 0; position: absolute; visibility: hidden;" v-html="svg"></div>
</template>

<script>
import fetch from 'unfetch';

export default {
    props: {
        src: {
            type: String,
            required: true,
        },
    },

    data () {
        return {
            svg: '',
        };
    },

    async mounted () {
        const response = await fetch(this.src);

        if (!response.ok) {
            if (console) {
                console.error('The SVG icons file at '+this.src+' could not be loaded.');
            }

            return;
        }

        this.svg = await response.text();
    },
}
</script>
