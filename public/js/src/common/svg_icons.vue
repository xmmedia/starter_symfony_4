<template>
    <div style="height: 0; width: 0; position: absolute; visibility: hidden;" v-html="svg"></div>
</template>

<script>
import api from '@/common/api';

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
        api
            .get(this.src)
            .then((response) => {
                this.svg = response.data;
            });
    },
}
</script>
