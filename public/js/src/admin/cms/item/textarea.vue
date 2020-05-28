<template>
    <div class="field-wrap">
        <label :for="id">{{ config.name }}</label>
        <field-error :v="v" />
        <textarea :id="id"
                  v-model="currentValue"
                  :rows="config.config.rows"
                  :maxlength="config.config.max" />
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div v-if="config.help" class="field-help" v-html="config.help"></div>
    </div>
</template>

<script>
import cuid from 'cuid';
import fieldError from '../field_error';

export default {
    components: {
        fieldError,
    },

    props: {
        value: {
            type: String,
            default: null,
        },
        config: {
            type: Object,
            required: true,
        },
        v: {
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
