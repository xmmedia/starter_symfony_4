<template>
    <div class="field-wrap">
        <label :for="id"><slot></slot></label>

        <field-error :v="v" />

        <input :id="id"
               :value="value"
               :maxlength="v.$params.maxLength.max"
               :autocomplete="autocomplete"
               type="text"
               v-on="inputListeners">

        <div v-if="hasHelp" class="field-help"><slot name="help"></slot></div>
    </div>
</template>

<script>
import cuid from 'cuid';
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

    computed: {
        hasHelp () {
            return !!this.$slots.help;
        },
    },
}
</script>
