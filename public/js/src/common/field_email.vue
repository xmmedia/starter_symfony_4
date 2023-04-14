<template>
    <div class="field-wrap">
        <label :for="id"><slot>Email (Username)</slot></label>

        <field-error v-if="v.$error && v.$invalid">
            <template v-if="v.required.$invalid">
                An email is required.
            </template>
            <template v-else-if="v.email.$invalid">
                This email is invalid.
            </template>
            <template v-else-if="v.unique.$invalid">
                This email has already been used.
            </template>
        </field-error>

        <input :id="id"
               ref="input"
               :value="modelValue"
               :autofocus="autofocus"
               :autocomplete="autocomplete"
               type="email"
               maxlength="150"
               @blur="checkEmail($event)"
               @input="$emit('update:modelValue', $event.target.value)">

        <div v-if="suggestedEmail" class="p-2 bg-emerald-900/70 text-white">
            Did you mean
            <button ref="suggestedEmailButton"
                    type="button"
                    class="button-link underline text-white mx-2 focus:text-white focus:ring-offset-red-700"
                    @click="useSuggested">{{ suggestedEmail }}</button>?
        </div>

        <div v-if="hasHelp" class="field-help"><slot name="help"></slot></div>
    </div>
</template>

<script>
import cuid from 'cuid';
import mailcheck from 'mailcheck';

export default {
    props: {
        modelValue: {
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
            if (!event.target.value) {
                return;
            }

            mailcheck.run({
                email: event.target.value,
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
            this.$emit('update:modelValue', this.suggestedEmail);
            this.suggestedEmail = null;
            this.$refs.input.focus();
        },
    },
}
</script>
