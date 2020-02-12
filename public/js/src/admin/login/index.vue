<template>
    <div>
        <portal to="header-page-title">Sign In</portal>

        <div class="form-wrap">
            <h1 class="mt-0 leading-none">Sign In</h1>

            <div v-if="errorMsg" class="alert alert-warning alert-type-warning">{{ errorMsg }}</div>

            <form method="post">
                <div class="field-wrap">
                    <label for="inputEmail">Email address</label>
                    <input id="inputEmail"
                           v-model="email"
                           type="email"
                           name="email"
                           required
                           autofocus
                           autocomplete="username email">
                </div>

                <field-password v-model="password"
                                name="password"
                                autocomplete="current-password" />

                <div class="field-wrap-checkbox">
                    <input id="remember_me"
                           type="checkbox"
                           name="_remember_me"
                           value="on">
                    <label for="remember_me">Remember me</label>
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
}
</script>
