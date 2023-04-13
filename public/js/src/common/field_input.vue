<template>
    <div class="field-wrap">
        <label :for="id"><slot></slot></label>

        <field-error v-if="v" :v="v">
            <template #required><slot name="required"></slot></template>
            <template #minLength><slot name="minLength"></slot></template>
            <template #maxLength><slot name="maxLength"></slot></template>
            <template #between><slot name="between"></slot></template>
            <template #minValue><slot name="minValue"></slot></template>
            <template #maxValue><slot name="maxValue"></slot></template>
            <template #url><slot name="url"></slot></template>
            <template #email><slot name="email"></slot></template>
            <template #valid><slot name="valid"></slot></template>
        </field-error>

        <input :id="id"
               ref="input"
               :value="value"
               :type="type"
               :maxlength="maxLength"
               :autocomplete="autocomplete"
               :placeholder="placeholder"
               v-on="inputListeners">

        <div v-if="hasHelp" class="field-help"><slot name="help"></slot></div>
    </div>
</template>

<script>
import cuid from 'cuid';
import has from 'lodash/has';
import fieldEventMixin from '@/common/field_event_mixin';

export default {
    mixins: [
        fieldEventMixin,
    ],

    props: {
        value: {
            type: String,
            default: null,
        },
        type: {
            type: String,
            default: 'text',
        },
        autocomplete: {
            type: String,
            default: null,
        },
        placeholder: {
            type: String,
            default: null,
        },
        v: {
            type: Object,
            default: null,
        },
        id: {
            type: String,
            default: function () {
                return cuid();
            },
        },
    },

    computed: {
        hasHelp () {
            return !!this.$slots.help;
        },

        maxLength () {
            if (!has(this.v, 'maxLength')) {
                return null;
            }

            return this.v.maxLength.$params.max;
        },
    },
}
</script>
