<template>
    <div class="form-wrap">
        <h1 class="mt-0 leading-none">Login</h1>

        <form method="post">
            <input :value="csrfToken" type="hidden" name="_csrf_token">

            <div class="field-wrap">
                <label for="inputEmail">Email Address</label>
                <input id="inputEmail"
                       v-model="email"
                       type="email"
                       name="email"
                       required
                       autofocus
                       autocomplete="username email">
            </div>

            <password-field v-model="password"
                            label="Password"
                            field="password"
                            autocomplete="current-password"/>

            <div class="field-wrap-checkbox">
                <input id="remember_me"
                       type="checkbox"
                       name="_remember_me"
                       value="on"><label
                           for="remember_me">Remember Me</label>
            </div>

            <div>
                <button class="button">Login</button>
                <a href="/recover" class="form-action">Forgot your password?</a>
            </div>
        </form>
    </div>
</template>

<script>
export default {
    components: {},

    props: {
        lastUsername: {
            type: String,
            default: null,
        },
        csrfToken: {
            type: String,
            required: true,
        },
    },

    data () {
        return {
            email: this.lastUsername,
            password: null,
        };
    },

    computed: {},

    watch: {},

    beforeMount () {},

    mounted () {},

    methods: {},
}
</script>
