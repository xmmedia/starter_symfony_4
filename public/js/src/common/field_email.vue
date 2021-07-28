<template>
    <div class="field-wrap">
        <label :for="id"><slot>Email (Username)</slot></label>

        <field-error v-if="v.$error">
            <template v-if="!v.required">
                An email is required.
            </template>
            <template v-else-if="!v.email">
                This email is invalid.
            </template>
            <template v-else-if="!v.unique">
                This email has already been used.
            </template>
        </field-error>

        <input :id="id"
               ref="input"
               :value="value"
               :autofocus="autofocus"
               :autocomplete="autocomplete"
               type="email"
               maxlength="150"
               @blur="checkEmail"
               v-on="inputListeners">

        <div v-if="suggestedEmail" class="p-2 bg-red-500 text-white">
            Did you mean
            <button ref="suggestedEmailButton"
                    type="button"
                    class="button-link underline text-white mx-2 focus:bg-transparent focus:mx-0 focus:px-2"
                    @click="useSuggested">{{ suggestedEmail }}</button>?
        </div>

        <div v-if="hasHelp" class="field-help"><slot name="help"></slot></div>
    </div>
</template>

<script>
import cuid from 'cuid';
import mailcheck from 'mailcheck';
import fieldEventMixin from './field_event_mixin';

export default {
    mixins: [
        fieldEventMixin,
    ],

    props: {
        value: {
            type: String,
            default: null,
        },
        autofocus: {
            type: Boolean,
            default: false,
        },
        autocomplete: {
            type: String,
            default: null,
        },
        v: {
            type: Object,
            required: true,
        },
        id: {
            type: String,
            default: function () {
                return cuid();
            },
        },
    },

    data () {
        return {
            suggestedEmail: null,
        };
    },

    computed: {
        hasHelp () {
            return !!this.$slots.help;
        },
    },

    methods: {
        checkEmail () {
            if (!this.value) {
                return;
            }

            mailcheck.run({
                email: this.value,
                suggested: (suggestion) => {
                    this.suggestedEmail = suggestion.full;

                    this.$nextTick(() => {
                        this.$refs.suggestedEmailButton.focus();
                    });
                },
                empty: () => {
                    this.suggestedEmail = null;
                },
            });
        },
        useSuggested () {
            this.$emit('input', this.suggestedEmail);
            this.suggestedEmail = null;
            this.$refs.input.focus();
        },
    },
}
</script>
