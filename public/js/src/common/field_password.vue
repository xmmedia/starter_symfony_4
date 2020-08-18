<template>
    <div class="field-wrap">
        <label :for="id"><slot>Password</slot></label>
        <slot name="errors"></slot>

        <div class="relative z-20">
            <input :id="id"
                   :value="value"
                   :type="fieldType"
                   :required="required"
                   :autocomplete="autocomplete"
                   :name="name"
                   class="pr-10 mb-1"
                   autocapitalize="off"
                   autocorrect="off"
                   spellcheck="false"
                   @input="$emit('input', $event.target.value)"
                   @focus="showMeter = true">
            <button type="button"
                    class="absolute button-link block top-0 right-0 w-6 h-6 mr-2 text-gray-600 hover:text-gray-800"
                    style="margin-top: 0.3rem;"
                    @click.prevent="visible = !visible">
                <svg class="w-6 h-6 fill-current" width="24" height="24">
                    <use :xlink:href="iconsPath+icon"></use>
                </svg>
            </button>
        </div>

        <password-score v-if="showHelp && showMeter"
                        :password="value"
                        :user-data="userData" />

        <div v-if="showHelp" class="field-help relative">
            Must be at least {{ minLength }} characters long.
        </div>
    </div>
</template>

<script>
import cuid from 'cuid';

import iconsPath from '@/../../images/icons-admin.svg';

export default {
    components: {
        'password-score': () => import(/* webpackChunkName: "password-score" */ './password_score'),
    },

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
        userData: {
            type: Array,
            default () {
                return [];
            },
        },
    },

    data () {
        return {
            id: cuid(),
            visible: false,
            showMeter: false,
            minLength: 12,
            iconsPath,
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
