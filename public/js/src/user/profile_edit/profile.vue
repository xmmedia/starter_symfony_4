<template>
    <div class="form-wrap my-8 p-0">
        <ProfileTabs />

        <form class="p-4" method="post" novalidate @submit.prevent="submit">
            <FormError v-if="v$.$error && v$.$invalid" />

            <FieldEmail :model-value="user.email"
                        :v="v$.user.email"
                        autofocus
                        autocomplete="username email"
                        @update:model-value="setEmailDebounce">
                Email address
            </FieldEmail>

            <div class="sm:flex gap-x-2">
                <FieldInput v-model="user.firstName"
                            :v="v$.user.firstName"
                            autocomplete="given-name"
                            class="grow"
                            @input="changed">First name</FieldInput>
                <FieldInput v-model="user.lastName"
                            :v="v$.user.lastName"
                            autocomplete="family-name"
                            class="grow"
                            @input="changed">Last name</FieldInput>
            </div>

            <FieldInput v-model="user.phoneNumber"
                        type="tel"
                        :v="v$.user.phoneNumber"
                        autocomplete="tel"
                        @input="changed">Phone number</FieldInput>

            <FormButton :saving="state.matches('saving')"
                        :saved="state.matches('saved')">
                Save Profile
                <template #cancel>
                    <button v-if="state.matches('edited')"
                            type="button"
                            class="form-action button-link"
                            @click.prevent="reset">Reset</button>
                </template>
            </FormButton>
        </form>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRootStore } from '@/user/stores/root';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useVuelidate } from '@vuelidate/core';
import { useMutation } from '@vue/apollo-composable';
import { formatPhone, logError } from '@/common/lib';
import ProfileTabs from './component/tabs.vue';
import FieldEmail from '@/common/field_email.vue';
import FieldInput from '@/common/field_input.vue';
import { UserUpdateProfile } from '@/user/queries/user.mutation.graphql';
import userValidation from './user.validation';
import debounce from 'lodash/debounce';

const rootStore = useRootStore();

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    states: {
        ready: {
            on: {
                SAVE: 'saving',
                EDITED: 'edited',
            },
        },
        edited: {
            on: {
                SAVE: 'saving',
                RESET: 'ready',
            },
        },
        saving: {
            on: {
                SAVED: 'saved',
                ERROR: 'ready',
            },
        },
        saved: {
            on: {
                RESET: 'ready',
                EDITED: 'edited',
            },
        },
    },
});
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const user = ref({});

const userValidations = userValidation();
const v$ = useVuelidate({
    user: {
        email: userValidations.email,
        firstName: userValidations.firstName,
        lastName: userValidations.lastName,
        phoneNumber: userValidations.phoneNumber,
    },
}, { user });

onMounted(() => {
    reset();
});

const setEmailDebounce = debounce(function (email) {
    setEmail(email);
}, 100, { leading: true });

function setEmail (value) {
    user.value.email = value;
    changed();
}

async function submit () {
    if (!state.value.matches('ready') && !state.value.matches('edited')) {
        return;
    }

    sendEvent({ type: 'SAVE' });

    if (!await v$.value.$validate()) {
        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);

        return;
    }

    try {
        const data = {
            email: user.value.email,
            firstName: user.value.firstName,
            lastName: user.value.lastName,
            userData: {
                phoneNumber: user.value.phoneNumber,
            },
        };

        const { mutate: sendUserUpdateProfile } = useMutation(UserUpdateProfile);
        await sendUserUpdateProfile({
            user: data,
        });

        rootStore.updateUser({
            ...data,
            name: data.firstName + ' ' + data.lastName,
        });

        sendEvent({ type: 'SAVED' });

        setTimeout(() => {
            sendEvent({ type: 'RESET' });
        }, 5000);

    } catch (e) {
        logError(e);
        alert('There was a problem saving your profile. Please try again later.');

        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);
    }
}

function changed () {
    sendEvent({ type: 'EDITED' });
}

function reset () {
    user.value.email = rootStore.user.email;
    user.value.firstName = rootStore.user.firstName;
    user.value.lastName = rootStore.user.lastName;
    user.value.phoneNumber = formatPhone(rootStore.user.userData?.phoneNumber || null);

    sendEvent({ type: 'RESET' });
}
</script>
