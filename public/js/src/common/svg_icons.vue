<template>
    <div style="height: 0; width: 0; position: absolute; visibility: hidden;" v-html="svg"></div>
</template>

<script>
import axios from '@/common/axios';

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

    mounted () {
        axios.get(this.src)
            .then((response) => {
                this.svg = response.data;
            });
    },
}
</script>
