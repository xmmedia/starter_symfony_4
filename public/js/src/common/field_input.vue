<template>
    <div class="field-wrap">
        <label :for="id"><slot></slot></label>

        <field-error :v="v" />

        <input :id="id"
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
            required: true,
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

            return this.v.$params.maxLength.max;
        },
    },
}
</script>
