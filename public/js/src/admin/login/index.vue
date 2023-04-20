<template>
    <div>
        <Portal to="header-page-title">Sign In</Portal>

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

                <FieldPassword id="inputPassword"
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
                    <RouterLink :to="{ name: 'user-recover-initiate' }" class="form-action">
                        Forgot your password?
                    </RouterLink>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useHead } from '@vueuse/head';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import { useQuery } from '@vue/apollo-composable';
import { AuthLast } from '@/admin/queries/auth.query.graphql';

const store = useStore();
const router = useRouter();

useHead({
    title: 'Login',
});

const email = ref(null);
const password = ref(null);
const errorMsg = ref(null);

const { onResult } = useQuery(AuthLast);
onResult(({ data: { AuthLast }}) => {
    if (!email.value) {
        email.value = AuthLast.email ?? null;
    }
    if (AuthLast.error) {
        errorMsg.value = AuthLast.error ?? null;
    }
});

onMounted(() => {
    if (store.getters.loggedIn) {
        router.replace({ name: 'login' });
    }
});
</script>
