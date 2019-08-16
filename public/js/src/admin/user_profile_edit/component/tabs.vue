<template>
    <ul class="list-none flex justify-between pl-0">
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
    methods: {
        linkActiveClass (linkRoute) {
            return {
                'bg-blue-100 hover:bg-blue-200' : this.$route.name !== linkRoute,
                'bg-blue-600 text-white' : this.$route.name === linkRoute,
            };
        },
    },
}
</script>
