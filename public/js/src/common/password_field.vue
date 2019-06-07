<template>
    <div class="field-wrap">
        <label :for="id" v-html="label"></label>
        <field-errors :errors="serverValidationErrors" />

        <div class="relative">
            <input :id="id"
                   :name="field"
                   :value="value"
                   :type="fieldType"
                   :required="required"
                   :autocomplete="autocomplete"
                   class="pr-10"
                   autocapitalize="off"
                   autocorrect="off"
                   spellcheck="false"
                   @input="$emit('input', $event.target.value)">
            <button type="button"
                    class="absolute button-link block top-0 right-0 w-6 h-6 mr-2 text-gray-600 hover:text-gray-700"
                    style="margin-top: 0.3rem;"
                    @click.prevent="visible = !visible">
                <svg class="w-6 h-6 fill-current"><use :xlink:href="icon"></use></svg>
            </button>
        </div>

        <div v-if="showHelp" class="field-help">
            Must be at least 12 characters long.
        </div>
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
        label: {
            type: String,
            required: true,
        },
        // server validation errors for field
        serverValidationErrors: {
            type: [Object, Array],
            default: function () {
                return {};
            },
        },
        // used to find the validation errors
        field: {
            type: String,
            required: true,
        },
        required: {
            type: Boolean,
            default: true,
        },
        autocomplete: {
            type: String,
            default: null,
        },
        showHelp: {
            type: Boolean,
            default: false,
        },
    },

    data () {
        return {
            id: cuid(),
            visible: false,
        };
    },

    computed: {
        fieldType () {
            return this.visible ? 'text' : 'password';
        },
        icon () {
            return this.visible ? '#visible' : '#invisible';
        },
    },
}
</script>
