<template>
    <div>
        <div v-if="status === 'loading'" class="italic">Loading users...</div>
        <div v-else-if="status === 'error'">There was a problem loading the user list. Please try again later.</div>

        <ul v-else-if="status === 'loaded'" class="record_list-wrap">
            <li class="record_list-headers">
                <div class="record_list-col">Username</div>
                <div class="record_list-col">Name</div>
                <div class="record_list-col">Account Status</div>
                <!--<div class="record_list-col">Last Login (Count)</div>-->
                <div class="record_list-col">Role</div>
                <div class="record_list-col"></div>
            </li>

            <!-- {% if user.locked or user.enabled == false %} record_list-item-inactive{% endif %} -->
            <li v-for="user in users" class="record_list-item">
                <div class="record_list-col">{{ user.email }}</div>
                <div class="record_list-col">{{ user.name }}</div>
                <div class="record_list-col">{{ user|accountStatus }}</div>
                <!--<div class="record_list-col"></div>-->
                <div class="record_list-col">{{ availableRoles[user.roles[0]] }}</div>

                <!--<div class="record_list-col user_list-last_login">
                    {% if user.loginCount %}
                    <a href="{{ path('xm_user_admin_login_history', { 'id' : user.id }) }}">
                        <local-time datetime="{{ user.lastLogin|date('c') }}">{{ user.lastLogin|date('Y-m-d H:i') }}</local-time> ({{ user.loginCount }})
                    </a>
                    {% else %}
                    <i>Never logged in</i>
                    {% endif %}
                </div>-->

                <div class="record_list-col record_list-col-actions">
                    <router-link :to="{ name: 'admin-user-edit', params: { userId: user.id } }">
                        Edit
                    </router-link>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
import { mapState } from 'vuex';
import { repositoryFactory } from '../repository/factory';
import { logError } from '@/common/lib';

const adminUserRepo = repositoryFactory.get('adminUser');

const statuses = {
    LOADING: 'loading',
    ERROR: 'error',
    LOADED: 'loaded',
};

export default {
    components: {},

    filters: {
        accountStatus (user) {
            if (!user.active) {
                return 'Inactive';
            } else if (!user.verified) {
                return 'Not Verified';
            }

            return 'Active';
        },
    },

    props: {},

    data () {
        return {
            status: statuses.LOADING,
            users: null,
        };
    },

    computed: {
        ...mapState([
            'availableRoles',
        ]),
    },

    watch: {},

    beforeMount () {},

    mounted () {
        this.load();
    },

    methods: {
        async load () {
            try {
                const response = await adminUserRepo.list();

                this.users = response.data.users;
                this.status = statuses.LOADED;

            } catch (e) {
                logError(e);

                this.status = statuses.ERROR;
            }
        },
    },
}
</script>
