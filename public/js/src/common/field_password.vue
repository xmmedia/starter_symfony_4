<template>
    <div class="field-wrap">
        <slot :id="id" name="label">
            <label :for="id"><slot>Password</slot></label>
        </slot>
        <slot name="errors"></slot>

        <div class="relative z-20">
            <input :id="id"
                   :value="modelValue"
                   :type="fieldType"
                   :required="required"
                   :autocomplete="autocomplete"
                   :name="name"
                   class="pr-10 mb-1"
                   autocapitalize="off"
                   autocorrect="off"
                   spellcheck="false"
                   @input="$emit('update:modelValue', $event.target.value)"
                   @focus="showMeter = true">
            <button type="button"
                    class="absolute button-link block top-0 right-0 w-6 h-6 mr-2
                           text-gray-600 hover:text-gray-800 focus:ring-offset-gray-100"
                    style="margin-top: 0.3rem;"
                    @click.prevent="visible = !visible">
                <component :is="iconComponent" :icon="icon" class="w-6 h-6 fill-current" width="24" height="24" />
                <span class="sr-only">Show password</span>
            </button>
        </div>

        <PasswordScore v-if="showHelp && showMeter"
                       :password="modelValue"
                       :user-data="userData" />

        <div v-if="showHelp" class="field-help relative">
            <slot name="help">
                Must be at least {{ minLength }} characters long.
            </slot>
        </div>
    </div>
</template>

<script setup>
import cuid from 'cuid';
import { computed, ref, watch } from 'vue';
import { passwordMinLength } from './validation/user.js';
import PasswordScore from './password_score.vue';

defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: {
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
    iconComponent: {
        type: String,
        default: 'AdminIcon',
    },
    id: {
        type: String,
        default: function () {
            return cuid();
        },
    },
});

const visible = ref(false);
const showMeter = ref(false);
const minLength = passwordMinLength;
const fieldType = computed(() => visible.value ? 'text' : 'password');
const icon = computed(() => visible.value ? 'visible' : 'invisible');

watch(() => props.modelValue, (modelValue) => {
    if (null === modelValue) {
        showMeter.value = false;
    }
});
</script>
