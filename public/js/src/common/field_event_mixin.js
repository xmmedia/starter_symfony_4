export default {
    inheritAttrs: false,

    computed: {
        inputListeners () {
            const vm = this;

            return Object.assign(
                {},
                this.$listeners,
                {
                    // This ensures that the component works with v-model
                    input: function (event) {
                        vm.$emit('input', event.target.value);
                    },
                },
            );
        },
    },
}
