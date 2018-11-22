<template>
    <div class="form-wrap">
        <h2 class="mt-0">Add User</h2>
        <form @submit.prevent="submit">
            <ul v-if="hasValidationErrors" class="field-errors mb-4" role="alert">
                <li>Please fix the errors below and submit your order again.</li>
            </ul>

            <div class="field-wrap">
                <label for="inputEmail">Email (Username)</label>
                <field-errors :errors="validationErrors" field="email" />
                <input id="inputEmail"
                       v-model="email"
                       type="email"
                       maxlength="150"
                       required
                       autofocus>
            </div>

            <div :class="{ 'mb-2' : setPassword }" class="field-wrap field-wrap-checkbox">
                <field-errors :errors="validationErrors" field="setPassword" />
                <input id="inputSetPassword" v-model="setPassword" type="checkbox">
                <label for="inputSetPassword">Set Password</label>
            </div>
            <div v-show="setPassword" class="field-wrap ml-6">
                <label for="inputPassword">Password</label>
                <field-errors :errors="validationErrors" field="password" />
                <input id="inputPassword"
                       ref="password"
                       v-model="password"
                       type="password"
                       required
                       maxlength="4096"
                       autocomplete="new-password">
            </div>

            <div class="field-wrap field-wrap-checkbox">
                <field-errors :errors="validationErrors" field="inputEnabled" />
                <input id="inputEnabled" v-model="enabled" type="checkbox">
                <label for="inputEnabled">Enabled</label>
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

            <div class="field-wrap">
                <label for="inputRole">Role</label>
                <field-errors :errors="validationErrors" field="role" />
                <select id="inputRole" v-model="role">
                    <option v-for="role in availableRoles"
                            :key="role.value"
                            :value="role.value">{{ role.name }}</option>
                </select>
            </div>

            <!-- @todo send invite -->

            <div>
                <button type="submit"
                        class="button"
                        @click.prevent="submit">Add User</button>

                <span v-if="status === 'saving'" class="ml-4 text-sm italic">Saving...</span>
                <span v-else-if="status === 'saved'" class="ml-4 text-sm italic">Saved</span>
            </div>
        </form>
    </div>
</template>

<script>
import { repositoryFactory } from '../repository/factory';
import { logError } from '@/common/lib';

const adminUserRepo = repositoryFactory.get('adminUser');

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

            email: null,
            setPassword: false,
            password: null,
            role: 'ROLE_USER',
            enabled: true,
            firstName: null,
            lastName: null,

            availableRoles: [
                { name: 'User', value: 'ROLE_USER' },
                { name: 'Admin', value: 'ROLE_ADMIN' },
                { name: 'Super Admin', value: 'ROLE_SUPER_ADMIN' },
            ],
        };
    },

    computed: {
        hasValidationErrors () {
            return Object.keys(this.validationErrors).length > 0;
        },
    },

    watch: {
        setPassword (val) {
            if (val) {
                this.$nextTick(() => {
                    this.$refs.password.focus();
                });
            }
        },
    },

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
                    enabled: this.enabled,
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
                    alert('There was a problem saving your profile. Please try again later.');
                }

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },
    },
}
</script>
