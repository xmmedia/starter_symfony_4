<template>
    <div class="field-wrap">
        <label :for="id" v-html="label"></label>
        <field-errors :errors="serverValidationErrors" />
        <slot name="errors"></slot>

        <field-error v-if="hackedPassword">
            It appears that this password was part of a data breach
            and may not be accepted. Consider using a different password.
        </field-error>

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
                    class="absolute button-link block top-0 right-0 w-6 h-6 mr-2 text-gray-600 hover:text-gray-800"
                    style="margin-top: 0.3rem;"
                    @click.prevent="visible = !visible">
                <svg class="w-6 h-6 fill-current" width="24" height="24">
                    <use :xlink:href="icon"></use>
                </svg>
            </button>
        </div>

        <div v-if="showHelp" class="field-help">
            Must be at least {{ minLength }} characters long.
        </div>
    </div>
</template>

<script>
import cuid from 'cuid';
import { pwnedPassword } from 'hibp';

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
            minLength: 12,
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

    asyncComputed: {
        hackedPassword: {
            async get () {
                if (this.autocomplete !== 'new-password') {
                    return false;
                }

                if (null === this.value || this.value.length < this.minLength) {
                    return false;
                }

                return await pwnedPassword(this.value) > 0;
            },
            default: false,
        },
    },
}
</script>
