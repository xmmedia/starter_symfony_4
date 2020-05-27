<template>
    <div class="field-wrap">
        <label :for="id">{{ config.name }}</label>
        <input :id="id"
               v-model="currentValue"
               type="text">
    </div>
</template>

<script>
import cuid from 'cuid';

export default {
    props: {
        value: {
            type: String,
            default: null,
        },
        config: {
            type: Object,
            required: true,
        },
    },

    data () {
        return {
            currentValue: null,
            id: cuid(),
        };
    },

    watch: {
        currentValue (value) {
            this.$emit('input', value);
        },
    },

    beforeMount () {
        this.currentValue = this.value;
    },

    updated () {
        // this.currentValue = this.value;
    },
};
</script>
