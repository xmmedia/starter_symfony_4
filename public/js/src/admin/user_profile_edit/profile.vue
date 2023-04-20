<template>
    <div class="form-wrap p-0">
        <ProfileTabs />

        <form class="p-4" method="post" @submit.prevent="submit">
            <FormError v-if="v$.$error && v$.$invalid" />

            <FieldEmail :model-value="email"
                        :v="v$.email"
                        autofocus
                        autocomplete="username email"
                        @update:modelValue="setEmailDebounce">
                Email address
            </FieldEmail>

            <FieldInput v-model.trim="firstName"
                        :v="v$.firstName"
                        autocomplete="given-name"
                        @input="changed">First name</FieldInput>
            <FieldInput v-model.trim="lastName"
                        :v="v$.lastName"
                        autocomplete="family-name"
                        @input="changed">Last name</FieldInput>

            <AdminButton :saving="state.matches('saving')"
                         :saved="state.matches('saved')">
                Save Profile
                <template #cancel>
                    <button v-if="state.matches('edited')"
                            class="form-action button-link"
                            @click.prevent="reset">Reset</button>
                </template>
            </AdminButton>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useStore } from 'vuex';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useVuelidate } from '@vuelidate/core';
import { useMutation } from '@vue/apollo-composable';
import cloneDeep from 'lodash/cloneDeep';
import { logError } from '@/common/lib';
import ProfileTabs from './component/tabs';
import FieldEmail from '@/common/field_email';
import FieldInput from '@/common/field_input';
import { UserUpdateProfile } from '../queries/user.mutation.graphql';
import userValidations from './user.validation';
import debounce from 'lodash/debounce';

const store = useStore();

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    strict: true,
    predictableActionArguments: true,
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

const { state, send: sendEvent } = useMachine(stateMachine);

const email = ref(store.state.user.email);
const firstName = ref(store.state.user.firstName);
const lastName = ref(store.state.user.lastName);

const v$ = useVuelidate({
    email: cloneDeep(userValidations.email),
    firstName: cloneDeep(userValidations.firstName),
    lastName: cloneDeep(userValidations.lastName),
}, { email, firstName, lastName });

const setEmailDebounce = debounce(function (email) {
    setEmail(email);
}, 100, { leading: true });
function setEmail (value) {
    email.value = value;
    changed();
}

async function submit () {
    if (!state.value.matches('ready') && !state.value.matches('edited')) {
        return;
    }

    sendEvent('SAVE');

    if (!await v$.value.$validate()) {
        sendEvent('ERROR');
        window.scrollTo(0, 0);

        return;
    }

    try {
        const data = {
            email: email.value,
            firstName: firstName.value,
            lastName: lastName.value,
        };

        const { mutate: sendUserUpdateProfile } = useMutation(UserUpdateProfile);
        await sendUserUpdateProfile({
            user: data,
        });

        await store.dispatch('updateUser', {
            ...data,
            name: data.firstName + ' ' + data.lastName,
        });

        sendEvent('SAVED');

        setTimeout(() => {
            sendEvent('RESET');
        }, 5000);

    } catch (e) {
        logError(e);
        alert('There was a problem saving your profile. Please try again later.');

        sendEvent('ERROR');
        window.scrollTo(0, 0);
    }
}

function changed () {
    sendEvent('EDITED');
}

function reset () {
    email.value = store.state.user.email;
    firstName.value = store.state.user.firstName;
    lastName.value = store.state.user.lastName;

    sendEvent('RESET');
}
</script>
