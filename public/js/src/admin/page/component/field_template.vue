<template>
    <div class="field-wrap">
        <label :for="id">Template</label>

        <!--<field-error v-if="v.$error">
            <template v-if="!v.required">
                An template is required.
            </template>
        </field-error>-->

        <select :id="id" v-model="template">
            <option v-for="_template in templates"
                    :value="_template.template"
                    :key="_template.template">
                {{ _template.name }}
            </option>
        </select>
    </div>
</template>

<script>
import cuid from 'cuid';

export default {
    props: {
        templates: {
            type: Array,
            default () {
                return [];
            },
        },
        value: {
            type: String,
            default: null,
        },
        // v: {
        //     type: Object,
        //     required: true,
        // },
    },

    data () {
        return {
            id: cuid(),
            template: null,
        };
    },

    watch: {
        template (value) {
            this.$emit('input', value);
        },
    },

    beforeMount () {
        this.template = this.value;
    },

    updated () {
        this.template = this.value;
    },
}
</script>
