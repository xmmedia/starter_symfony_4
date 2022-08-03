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
                    <slot name="minLength" :v="v">
                        Must be at least {{ v.$params.minLength.min }}
                        {{ 'character'|pluralize(v.$params.minLength.min) }}.
                    </slot>
                </template>
                <template v-else-if="!maxLength">
                    <slot name="maxLength" :v="v">
                        Cannot be more than {{ v.$params.maxLength.max }}
                        {{ 'character'|pluralize(v.$params.maxLength.max) }}.
                    </slot>
                </template>
                <template v-else-if="!between">
                    <slot name="between" :v="v">
                        Must be between {{ v.$params.between.min }}
                        and {{ v.$params.between.max }}.
                    </slot>
                </template>
                <template v-else-if="!minValue">
                    <slot name="minValue" :v="v">
                        Must be {{ v.$params.minValue.min }} or more.
                    </slot>
                </template>
                <template v-else-if="!maxValue">
                    <slot name="maxValue" :v="v">
                        Must be less than or equal to {{ v.$params.maxValue.max }}.
                    </slot>
                </template>
                <template v-else-if="!url">
                    <slot name="url" :v="v">
                        The URL is not valid.
                    </slot>
                </template>
                <template v-else-if="!email">
                    <slot name="email" :v="v">
                        This email is invalid.
                    </slot>
                </template>
                <template v-else-if="!valid">
                    <slot name="valid" :v="v">
                        This is not a valid value.
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
        minValue () {
            return this.vuelidateValue('minValue');
        },
        maxValue () {
            return this.vuelidateValue('maxValue');
        },
        required () {
            return this.vuelidateValue('required');
        },
        url () {
            return this.vuelidateValue('url');
        },
        valid () {
            return this.vuelidateValue('valid');
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
