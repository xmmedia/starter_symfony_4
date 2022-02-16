<template>
    <div>
        <portal to="header-page-title">Sign In</portal>

        <div class="form-wrap">
            <h1 class="mt-0 leading-none">Sign In</h1>

            <div v-if="errorMsg" class="alert alert-warning alert-type-warning">{{ errorMsg }}</div>

            <form method="post">
                <!-- field names match what Symfony uses by default -->
                <div class="field-wrap">
                    <label for="inputEmail">Email address</label>
                    <input id="inputEmail"
                           v-model="email"
                           type="email"
                           name="_username"
                           required
                           autofocus
                           autocomplete="username email">
                </div>

                <field-password id="inputPassword"
                                v-model="password"
                                name="_password"
                                autocomplete="current-password" />

                <div class="field-wrap field-wrap-checkbox">
                    <input id="rememberMe"
                           type="checkbox"
                           name="_remember_me"
                           value="on">
                    <label for="rememberMe">Remember me</label>
                </div>

                <div>
                    <button class="button">Sign In</button>
                    <router-link :to="{ name: 'user-recover-initiate' }" class="form-action">
                        Forgot your password?
                    </router-link>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { AuthLast } from '../queries/auth.query.graphql';

export default {
    metaInfo: {
        title: 'Login',
    },

    data () {
        return {
            email: null,
            password: null,
            errorMsg: null,
        };
    },

    apollo: {
        lastAuth: {
            query: AuthLast,
            update (data) {
                if (!this.email) {
                    this.email = data.AuthLast.email;
                }
                if (data.AuthLast.error) {
                    this.errorMsg = data.AuthLast.error;
                }
            },
        },
    },

    mounted () {
        if (this.$store.getters.loggedIn) {
            this.$router.replace({ name: 'login' });
        }
    },
}
</script>
