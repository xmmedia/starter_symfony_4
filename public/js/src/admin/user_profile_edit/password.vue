<template>
    <div class="form-wrap p-0">
        <profile-tabs />

        <div class="p-4">
            <form @submit.prevent="submit">
                <ul v-if="hasValidationErrors" class="field-errors mb-4" role="alert">
                    <li>Please fix the errors below.</li>
                </ul>

                <div class="hidden">
                    <label for="inputEmail">Email</label>
                    <input id="inputEmail"
                           :value="$store.state.serverData.user.email"
                           type="email"
                           name="email"
                           autocomplete="username email">
                </div>

                <password-field v-model="currentPassword"
                                :validation-errors="validationErrors"
                                label="Current Password"
                                field="currentPassword"
                                autocomplete="current-password" />

                <password-field v-model="newPassword"
                                :validation-errors="validationErrors"
                                label="New Password"
                                field="newPassword.first"
                                autocomplete="new-password" />
                <password-field v-model="repeatPassword"
                                :validation-errors="validationErrors"
                                label="New Password Again"
                                field="newPassword.second"
                                autocomplete="new-password" />

                <div>
                    <button type="submit" class="button">Change Password</button>
                    <router-link :to="{ name: 'user-profile-edit' }"
                                 class="form-action">Cancel</router-link>

                    <span v-if="status === 'saving'" class="ml-4 text-sm italic">Saving...</span>
                    <span v-else-if="status === 'saved'" class="ml-4 text-sm italic">Saved</span>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { repositoryFactory } from '../repository/factory';
import { logError } from '@/common/lib';
import profileTabs from './component/tabs';

const userProfileEditRepo = repositoryFactory.get('userProfileEdit');

const statuses = {
    LOADED: 'loaded',
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

            currentPassword: null,
            newPassword: null,
            repeatPassword: null,
        };
    },

    computed: {
        hasValidationErrors () {
            return Object.keys(this.validationErrors).length > 0;
        },
    },

    methods: {
        async submit () {
            this.status = statuses.SAVING;

            try {
                const data = {
                    currentPassword: this.currentPassword,
                    newPassword: {
                        first: this.newPassword,
                        second: this.repeatPassword,
                    },
                };

                await userProfileEditRepo.password(data);

                this.currentPassword = null;
                this.newPassword = null;
                this.repeatPassword = null;

                this.status = statuses.SAVED;
                this.validationErrors = {};

                setTimeout(() => {
                    this.status = statuses.LOADED;
                }, 5000);

            } catch (e) {
                if (e.response && e.response.status === 400) {
                    this.validationErrors = e.response.data.errors;
                } else {
                    logError(e);
                    alert('There was a problem saving your password. Please try again later.');
                }

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },
    },
}
</script>
