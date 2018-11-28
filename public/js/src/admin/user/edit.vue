<template>
    <div class="form-wrap">
        <h2 class="mt-0">Edit User</h2>
        <div v-if="status === 'loading'" class="italic">Loading user...</div>
        <div v-else-if="status === 'error'">There was a problem loading the user list. Please try again later.</div>

        <form v-else-if="showForm" @submit.prevent="submit">
            <ul v-if="hasValidationErrors" class="field-errors mb-4" role="alert">
                <li>Please fix the errors below and try again.</li>
            </ul>

            <field-email v-model="email" :validation-errors="validationErrors" />

            <field-password v-model="password"
                            :validation-errors="validationErrors"
                            checkbox-label="Change Password"
                            @set-password="changePassword = $event" />

            <field-name v-model="firstName"
                        :validation-errors="validationErrors"
                        label="First Name"
                        field="firstName" />
            <field-name v-model="lastName"
                        :validation-errors="validationErrors"
                        label="Last Name"
                        field="lastName" />

            <field-role v-model="role" :validation-errors="validationErrors" />

            <div>
                <button type="submit" class="button">Update User</button>
                <router-link :to="{ name: 'admin-user' }"
                             class="form-action">Cancel</router-link>

                <span v-if="status === 'saving'" class="ml-4 text-sm italic">Saving...</span>
                <span v-else-if="status === 'saved'" class="ml-4 text-sm italic">Saved</span>
            </div>

            <ul class="form-extra_actions">
                <li>
                    <button v-if="verified"
                            class="button-link form-action"
                            @click.prevent="toggleActive"
                            v-html="activeButtonText"></button>
                    <button v-else
                            class="button-link form-action"
                            @click.prevent="verify">Manually Verify User</button>
                </li>
                <li v-if="active">
                    <button class="button-link form-action"
                            @click.prevent="sendReset">Send Password Reset</button>
                </li>
            </ul>
        </form>
    </div>
</template>

<script>
import { repositoryFactory } from '../repository/factory';
import { logError } from '@/common/lib';
import fieldEmail from './component/email';
import fieldPassword from './component/password';
import fieldName from './component/name';
import fieldRole from './component/role';

const adminUserRepo = repositoryFactory.get('adminUser');

const statuses = {
    LOADING: 'loading',
    ERROR: 'error',
    LOADED: 'loaded',
    SAVING: 'saving',
    SAVED: 'saved',
};

export default {
    components: {
        'field-email': fieldEmail,
        'field-password': fieldPassword,
        'field-name': fieldName,
        'field-role': fieldRole,
    },

    props: {},

    data () {
        return {
            status: statuses.LOADING,
            validationErrors: {},

            userId: this.$route.params.userId,
            email: null,
            changePassword: false,
            password: null,
            role: 'ROLE_USER',
            firstName: null,
            lastName: null,
            verified: false,
            active: false,
        };
    },

    computed: {
        showForm () {
            return [statuses.LOADED, statuses.SAVING, statuses.SAVED].includes(this.status);
        },
        allowSave () {
            return [statuses.LOADED, statuses.SAVED].includes(this.status);
        },
        hasValidationErrors () {
            return Object.keys(this.validationErrors).length > 0;
        },

        activeButtonText () {
            return this.active ? 'Deactivate User' : 'Activate User';
        },
    },

    watch: {},

    beforeMount () {},

    mounted () {
        this.load();
    },

    methods: {
        async load () {
            try {
                const response = await adminUserRepo.get(this.userId);

                this.email = response.data.user.email;
                this.role = response.data.user.roles[0];
                this.firstName = response.data.user.firstName;
                this.lastName = response.data.user.lastName;
                this.verified = response.data.user.verified;
                this.active = response.data.user.active;

                this.status = statuses.LOADED;

            } catch (e) {
                logError(e);

                this.status = statuses.ERROR;
            }
        },

        async submit () {
            if (!this.allowSave) {
                return;
            }

            this.status = statuses.SAVING;

            try {
                const data = {
                    id: this.userId,
                    email: this.email,
                    changePassword: this.changePassword,
                    password: this.password,
                    role: this.role,
                    firstName: this.firstName,
                    lastName: this.lastName,
                };

                await adminUserRepo.update(data);

                this.status = statuses.SAVED;
                this.validationErrors = {};

                setTimeout(() => {
                    this.$router.push({ name: 'admin-user' });
                }, 1500);

            } catch (e) {
                if (e.response && e.response.status === 400) {
                    this.validationErrors = e.response.data.errors;
                } else {
                    logError(e);
                    alert('There was a problem saving. Please try again later.');
                }

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },

        async toggleActive () {
            if (!this.allowSave) {
                return;
            }

            this.status = statuses.SAVING;

            try {
                if (!this.active) {
                    await adminUserRepo.activate(this.userId);
                } else {
                    await adminUserRepo.deactivate(this.userId);
                }

                this.active = !this.active;

                this.status = statuses.SAVED;

                setTimeout(() => {
                    this.status = statuses.LOADED;
                }, 3000);

            } catch (e) {
                logError(e);
                alert('There was a problem toggling the active state. Please try again later.');

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },

        async verify () {
            if (!this.allowSave) {
                return;
            }

            this.status = statuses.SAVING;

            try {
                await adminUserRepo.verify(this.userId);

                this.verify = true;
                this.status = statuses.SAVED;

                setTimeout(() => {
                    this.status = statuses.LOADED;
                }, 3000);

            } catch (e) {
                logError(e);
                alert('There was a problem verifying the user. Please try again later.');

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },

        async sendReset () {
            if (!this.allowSave) {
                return;
            }

            this.status = statuses.SAVING;

            try {
                await adminUserRepo.sendReset(this.userId);

                this.status = statuses.SAVED;

                setTimeout(() => {
                    this.status = statuses.LOADED;
                }, 3000);

            } catch (e) {
                logError(e);
                alert('There was a problem sending the reset. Please try again later.');

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },
    },
}
</script>
