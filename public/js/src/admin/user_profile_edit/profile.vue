<template>
    <div class="form-wrap p-0">
        <profile-tabs />

        <form class="p-4" method="post" @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />

            <field-email v-model="email"
                         :v="$v.email"
                         autofocus
                         autocomplete="username email"
                         @input="changed">
                Email Address
            </field-email>

            <field-name v-model="firstName"
                        :v="$v.firstName"
                        autocomplete="given-name"
                        @input="changed">First Name</field-name>
            <field-name v-model="lastName"
                        :v="$v.lastName"
                        autocomplete="family-name"
                        @input="changed">Last Name</field-name>

            <admin-button :status="status">
                Save Profile
                <button slot="cancel"
                        class="form-action button-link"
                        @click.prevent="reset">Reset</button>
            </admin-button>
        </form>
    </div>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep';
import { waitForValidation } from '@/common/lib';
import fieldEmail from '@/common/field_email';
import fieldName from '@/common/field_name';
import profileTabs from './component/tabs';
import { UserUpdateProfile } from '../queries/user.mutation.graphql';

import userValidations from './user.validation';

const statuses = {
    LOADED: 'loaded',
    EDITED: 'edited',
    SAVING: 'saving',
    SAVED: 'saved',
};

export default {
    components: {
        profileTabs,
        fieldEmail,
        fieldName,
    },

    data () {
        return {
            status: statuses.LOADED,

            email: this.$store.state.user.email,
            firstName: this.$store.state.user.firstName,
            lastName: this.$store.state.user.lastName,
        };
    },

    beforeRouteLeave (to, from, next) {
        if (this.status === statuses.EDITED) {
            if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                return
            }
        }

        next();
    },

    validations () {
        return {
            email: cloneDeep(userValidations.email),
            firstName: cloneDeep(userValidations.firstName),
            lastName: cloneDeep(userValidations.lastName),
        };
    },

    methods: {
        waitForValidation,

        async submit () {
            this.status = statuses.SAVING;

            this.$v.$touch();

            if (!await this.waitForValidation()) {
                this.status = statuses.LOADED;
                window.scrollTo(0, 0);

                return;
            }

            try {
                await this.$apollo.mutate({
                    mutation: UserUpdateProfile,
                    variables: {
                        user: {
                            email: this.email,
                            firstName: this.firstName,
                            lastName: this.lastName,
                        },
                    },
                });

                this.status = statuses.SAVED;

                this.$store.dispatch('updateUser', {
                    email: this.email,
                    name: this.firstName + ' ' + this.lastName,
                });

                setTimeout(() => {
                    if (this.status === statuses.SAVED) {
                        this.status = statuses.LOADED;
                    }
                }, 5000);

            } catch (e) {
                alert('There was a problem saving your profile. Please try again later.');
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
