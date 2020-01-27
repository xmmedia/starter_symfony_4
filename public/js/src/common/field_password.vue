<template>
    <div class="field-wrap">
        <label :for="id"><slot>Password</slot></label>
        <slot name="errors"></slot>

        <div class="relative">
            <input :id="id"
                   :value="value"
                   :type="fieldType"
                   :required="required"
                   :autocomplete="autocomplete"
                   :name="name"
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

export default {
    props: {
        value: {
            type: String,
            default: null,
        },
        name: {
            type: String,
            default: null,
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
}
</script>
