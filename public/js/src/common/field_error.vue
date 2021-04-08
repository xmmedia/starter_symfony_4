<template>
    <ul v-if="hasErrors"
        class="field-errors"
        role="alert"
        aria-live="polite">
        <li>
            <slot>
                <template v-if="!required">
                    <slot name="required">Required</slot>
                </template>
                <template v-else-if="!minLength">
                    <slot name="minLength">
                        Must be at least {{ v.$params.minLength.min }}
                        {{ 'character'|pluralize(v.$params.minLength.min) }}.
                    </slot>
                </template>
                <template v-else-if="!maxLength">
                    <slot name="maxLength">
                        Cannot be more than {{ v.$params.maxLength.max }}
                        {{ 'character'|pluralize(v.$params.minLength.min) }}.
                    </slot>
                </template>
                <template v-else-if="!between">
                    <slot name="between">
                        Must be between {{ v.$params.between.min }}
                        and {{ v.$params.between.max }}.
                    </slot>
                </template>
                <template v-else-if="!url">
                    <slot name="url">
                        The URL is not valid.
                    </slot>
                </template>
                <template v-else-if="!email">
                    <slot name="email">
                        This email is invalid.
                    </slot>
                </template>
            </slot>
        </li>
    </ul>
</template>

<script>
import has from 'lodash/has';

export default {
    props: {
        v: {
            type: Object,
            default: null,
        },
    },

    computed: {
        hasErrors () {
            if (this.v) {
                return this.v.$error;
            }

            return !!this.$slots.default;
        },

        between () {
            return this.vuelidateValue('between');
        },
        email () {
            return this.vuelidateValue('email');
        },
        maxLength () {
            return this.vuelidateValue('maxLength');
        },
        minLength () {
            return this.vuelidateValue('minLength');
        },
        required () {
            return this.vuelidateValue('required');
        },
        url () {
            return this.vuelidateValue('url');
        },
    },

    methods: {
        vuelidateValue (key) {
            if (!this.hasVuelidateProp(key)) {
                return true;
            }

            return this.v[key];
        },
        hasVuelidateProp (key) {
            return has(this.v, key);
        },
    },
}
</script>
