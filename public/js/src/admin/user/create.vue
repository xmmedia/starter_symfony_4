<template>
    <div class="form-wrap">
        <h2 class="mt-0">Add User</h2>
        <form @submit.prevent="submit">
            <ul v-if="hasValidationErrors" class="field-errors mb-4" role="alert">
                <li>Please fix the errors below and try again.</li>
            </ul>

            <field-email v-model="email" :validation-errors="validationErrors" />

            <field-password v-model="password"
                            :validation-errors="validationErrors"
                            checkbox-label="Set Password"
                            @set-password="setPassword = $event" />

            <div class="field-wrap field-wrap-checkbox">
                <field-errors :errors="validationErrors" field="active" />
                <input id="inputActive" v-model="active" type="checkbox">
                <label for="inputActive">Active</label>
            </div>

            <field-name v-model="firstName"
                        :validation-errors="validationErrors"
                        label="First Name"
                        field="firstName" />
            <field-name v-model="lastName"
                        :validation-errors="validationErrors"
                        label="Last Name"
                        field="lastName" />

            <field-role v-model="role" :validation-errors="validationErrors" />

            <!-- @todo send invite & not verified -->

            <div>
                <button type="submit"
                        class="button"
                        @click.prevent="submit">Add User</button>
                <router-link :to="{ name: 'admin-user' }"
                             class="form-action">Cancel</router-link>

                <span v-if="status === 'saving'" class="ml-4 text-sm italic">Saving...</span>
                <span v-else-if="status === 'saved'" class="ml-4 text-sm italic">Saved</span>
            </div>
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
            status: statuses.LOADED,
            validationErrors: {},

            email: null,
            setPassword: false,
            password: null,
            role: 'ROLE_USER',
            active: true,
            firstName: null,
            lastName: null,
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
                    setPassword: this.setPassword,
                    password: this.password,
                    role: this.role,
                    active: this.active,
                    firstName: this.firstName,
                    lastName: this.lastName,
                };

                await adminUserRepo.create(data);

                this.status = statuses.SAVED;
                this.validationErrors = {};

                this.$router.push({ name: 'admin-user' });

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
    },
}
</script>
