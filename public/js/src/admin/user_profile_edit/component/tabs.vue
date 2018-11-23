<template>
    <ul class="list-reset flex justify-between">
        <li class="flex-grow">
            <router-link :to="{ name: 'user-profile-edit' }"
                         :class="linkActiveClass('user-profile-edit')"
                         class="block p-4 text-center hover:no-underline rounded-tl-sm">
                Profile
            </router-link>
        </li>
        <li class="flex-grow">
            <router-link :to="{ name: 'user-profile-edit-password' }"
                         :class="linkActiveClass('user-profile-edit-password')"
                         class="block p-4 text-center hover:no-underline rounded-tr-sm">
                Password
            </router-link>
        </li>
    </ul>
</template>

<script>
export default {
    components: {},

    props: {},

    data () {
        return {};
    },

    computed: {},

    watch: {},

    beforeMount () {},

    mounted () {},

    methods: {
        linkActiveClass (linkRoute) {
            return {
                'bg-blue-lightest hover:bg-blue-lighter' : this.$route.name !== linkRoute,
                'bg-blue-dark text-white' : this.$route.name === linkRoute,
            };
        },
    },
}
</script>
