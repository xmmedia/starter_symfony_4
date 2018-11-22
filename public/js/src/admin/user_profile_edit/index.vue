<template>
    <div class="form-wrap">
        <form @submit.prevent="submit">
            <ul v-if="hasValidationErrors" class="field-errors mb-4" role="alert">
                <li>Please fix the errors below and save your profile again.</li>
            </ul>

            <div class="field-wrap">
                <label for="inputEmail">Email Address</label>
                <field-errors :errors="validationErrors" field="email" />
                <input id="inputEmail"
                       v-model="email"
                       type="email"
                       maxlength="150"
                       required
                       autofocus>
            </div>
            <div class="field-wrap">
                <label for="inputFirstName">First Name</label>
                <field-errors :errors="validationErrors" field="firstName" />
                <input id="inputFirstName"
                       v-model="firstName"
                       type="text"
                       required
                       maxlength="50">
            </div>
            <div class="field-wrap">
                <label for="inputLastName">Last Name</label>
                <field-errors :errors="validationErrors" field="lastName" />
                <input id="inputLastName"
                       v-model="lastName"
                       type="text"
                       required
                       maxlength="50">
            </div>

            <div>
                <button type="submit"
                        class="button"
                        @click.prevent="submit">Save Profile</button>

                <span v-if="status === 'saving'" class="ml-4 text-sm italic">Saving...</span>
                <span v-else-if="status === 'saved'" class="ml-4 text-sm italic">Saved</span>
            </div>
        </form>
    </div>
</template>

<script>
import { repositoryFactory } from '../repository/factory';
import { logError } from '@/common/lib';

const userProfileEditRepo = repositoryFactory.get('userProfileEdit');

const statuses = {
    LOADED: 'loaded',
    SAVING: 'saving',
    SAVED: 'saved',
};

export default {
    components: {},

    props: {},

    data () {
        return {
            status: statuses.LOADED,
            validationErrors: {},

            email: this.$store.state.serverData.user.email,
            firstName: this.$store.state.serverData.user.firstName,
            lastName: this.$store.state.serverData.user.lastName,
        };
    },

    computed: {
        hasValidationErrors () {
            return Object.keys(this.validationErrors).length > 0;
        },
    },

    watch: {},

    beforeMount () {},

    mounted () {},

    methods: {
        async submit () {
            this.status = statuses.SAVING;

            try {
                const data = {
                    email: this.email,
                    firstName: this.firstName,
                    lastName: this.lastName,
                };

                await userProfileEditRepo.save(data);

                this.status = statuses.SAVED;
                this.validationErrors = {};

                this.$store.dispatch('updateUser', data);

                setTimeout(() => {
                    this.status = statuses.LOADED;
                }, 5000);

            } catch (e) {
                if (e.response && e.response.status === 400) {
                    this.validationErrors = e.response.data.errors;
                } else {
                    logError(e);
                    alert('There was a problem saving your profile. Please try again later.');
                }

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },
    },
}
</script>
