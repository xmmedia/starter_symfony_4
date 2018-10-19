export default {
    data() {
        return {
            setPassword: false,
        };
    },
    watch: {
        setPassword() {
            this.$refs.password.focus();
        }
    }
}