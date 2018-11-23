<template>
    <div>
        <div :class="{ 'mb-2' : setPassword }" class="field-wrap field-wrap-checkbox">
            <field-errors :errors="validationErrors" field="setPassword" />
            <input id="inputSetPassword" v-model="setPassword" type="checkbox">
            <label for="inputSetPassword">{{ checkboxLabel }}</label>
        </div>
        <div v-show="setPassword" class="field-wrap ml-6">
            <label for="inputPassword">Password</label>
            <field-errors :errors="validationErrors" field="password" />
            <input id="inputPassword"
                   ref="password"
                   :value="value"
                   type="password"
                   required
                   maxlength="4096"
                   autocomplete="new-password"
                   @input="$emit('input', $event.target.value)">
        </div>
    </div>
</template>

<script>
export default {
    props: {
        value: {
            type: String,
            default: null,
        },
        checkboxLabel: {
            type: String,
            default: 'Set Password',
        },
        validationErrors: {
            type: Object,
            default: function () {
                return [];
            },
        },
    },

    data () {
        return {
            setPassword: false,
        };
    },

    watch: {
        setPassword (val) {
            this.$emit('set-password', val);

            if (val) {
                this.$nextTick(() => {
                    this.$refs.password.focus();
                });
            }
        },
    },
}
</script>
