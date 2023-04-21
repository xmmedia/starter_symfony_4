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
                        Must be at least {{ v.minLength.$params.min }}
                        {{ pluralize('character', v.minLength.$params.min) }}.
                    </slot>
                </template>
                <template v-else-if="!maxLength">
                    <slot name="maxLength" :v="v">
                        Cannot be more than {{ v.maxLength.$params.max }}
                        {{ pluralize('character', v.maxLength.$params.max) }}.
                    </slot>
                </template>
                <template v-else-if="!between">
                    <slot name="between" :v="v">
                        Must be between {{ v.between.$params.min }}
                        and {{ v.between.$params.max }}.
                    </slot>
                </template>
                <template v-else-if="!minValue">
                    <slot name="minValue" :v="v">
                        Must be {{ v.minValue.$params.min }} or more.
                    </slot>
                </template>
                <template v-else-if="!maxValue">
                    <slot name="maxValue" :v="v">
                        Must be less than or equal to {{ v.maxValue.$params.max }}.
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
                <template v-else-if="!unique">
                    <slot name="unique" :v="v">
                        This value is not unique.
                    </slot>
                </template>
                <template v-else-if="!alpha">
                    <slot name="alpha" :v="v">
                        This must be all letters.
                    </slot>
                </template>
            </slot>
        </li>
    </ul>
</template>

<script setup>
import { computed, useSlots } from 'vue';
import { vuelidateValue, pluralize } from './lib';

const slots = useSlots();

const props = defineProps({
    v: {
        type: Object,
        default: null,
    },
});

const hasErrors = computed(() => {
    if (props.v) {
        return props.v.$error && props.v.$invalid;
    }

    return !!slots.default;
});

const between = computed(() => vuelidateValue(props.v, 'between'));
const email = computed(() => vuelidateValue(props.v, 'email'));
const maxLength = computed(() => vuelidateValue(props.v, 'maxLength'));
const minLength = computed(() => vuelidateValue(props.v, 'minLength'));
const minValue = computed(() => vuelidateValue(props.v, 'minValue'));
const maxValue = computed(() => vuelidateValue(props.v, 'maxValue'));
const required = computed(() => vuelidateValue(props.v, 'required'));
const url = computed(() => vuelidateValue(props.v, 'url'));
const valid = computed(() => vuelidateValue(props.v, 'valid'));
const unique = computed(() => vuelidateValue(props.v, 'unique'));
const alpha = computed(() => vuelidateValue(props.v, 'alpha'));
</script>
