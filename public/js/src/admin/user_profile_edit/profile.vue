<template>
    <div class="form-wrap p-0">
        <profile-tabs />

        <div class="p-4">
            <form @submit.prevent="submit">
                <form-error v-if="hasValidationErrors" />

                <div class="field-wrap">
                    <label for="inputEmail">Email Address</label>
                    <field-errors :errors="validationErrors" field="email" />
                    <input id="inputEmail"
                           v-model="email"
                           type="email"
                           maxlength="150"
                           required
                           autofocus
                           autocomplete="username email"
                           @input="changed">
                </div>
                <div class="field-wrap">
                    <label for="inputFirstName">First Name</label>
                    <field-errors :errors="validationErrors" field="firstName" />
                    <input id="inputFirstName"
                           v-model="firstName"
                           type="text"
                           required
                           maxlength="50"
                           autocomplete="given-name"
                           @input="changed">
                </div>
                <div class="field-wrap">
                    <label for="inputLastName">Last Name</label>
                    <field-errors :errors="validationErrors" field="lastName" />
                    <input id="inputLastName"
                           v-model="lastName"
                           type="text"
                           required
                           maxlength="50"
                           autocomplete="family-name"
                           @input="changed">
                </div>

                <div>
                    <button type="submit" class="button">Save Profile</button>
                    <button class="form-action button-link"
                            @click.prevent="reset">Reset</button>

                    <span v-if="status === 'saving'" class="ml-4 text-sm italic">Saving...</span>
                    <span v-else-if="status === 'saved'" class="ml-4 text-sm italic">Saved</span>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { logError, hasGraphQlValidationError } from '@/common/lib';
import profileTabs from './component/tabs';
import { UserUpdateProfile } from '../queries/user.mutation';

const statuses = {
    LOADED: 'loaded',
    EDITED: 'edited',
    SAVING: 'saving',
    SAVED: 'saved',
};

export default {
    components: {
        'profile-tabs': profileTabs,
    },

    data () {
        return {
            status: statuses.LOADED,
            validationErrors: {},

            email: this.$store.state.user.email,
            firstName: this.$store.state.user.firstName,
            lastName: this.$store.state.user.lastName,
        };
    },

    computed: {
        hasValidationErrors () {
            return Object.keys(this.validationErrors).length > 0;
        },
    },

    beforeRouteLeave (to, from, next) {
        if (this.status === statuses.EDITED) {
            if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                return
            }
        }

        next();
    },

    methods: {
        async submit () {
            this.status = statuses.SAVING;

            try {
                const data = {
                    email: this.email,
                    firstName: this.firstName,
                    lastName: this.lastName,
                };

                await this.$apollo.mutate({
                    mutation: UserUpdateProfile,
                    variables: {
                        user: data,
                    },
                });

                this.status = statuses.SAVED;
                this.validationErrors = {};

                this.$store.dispatch('updateUser', {
                    ...data,
                    name: data.firstName + ' ' + data.lastName,
                });

                setTimeout(() => {
                    if (this.status === statuses.SAVED) {
                        this.status = statuses.LOADED;
                    }
                }, 5000);

            } catch (e) {
                if (hasGraphQlValidationError(e)) {
                    this.validationErrors = e.graphQLErrors[0].validation.user;
                } else {
                    logError(e);
                    alert('There was a problem saving your profile. Please try again later.');
                }

                window.scrollTo(0, 0);

                this.status = statuses.EDITED;
            }
        },

        changed () {
            this.status = statuses.EDITED;
        },

        reset () {
            this.email = this.$store.state.user.email;
            this.firstName = this.$store.state.user.firstName;
            this.lastName = this.$store.state.user.lastName;

            this.status = statuses.LOADED;
        },
    },
}
</script>
