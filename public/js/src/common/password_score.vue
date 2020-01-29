<template>
    <div class="my-2 text-xs">
        <div class="w-full bg-gray-500">
            <span class="float-left py-1 pl-2 text-white">
                Strength<template v-if="passwordWarning">:
                    {{ passwordWarning }}
                </template>
            </span>
            <div :class="scoreBackgroundClasses" class="h-6"></div>
        </div>
    </div>
</template>

<script>
    import zxcvbn from 'zxcvbn';
    import userValidation from '@/admin/validation/user';

    export default {
        props: {
            password: {
                type: String,
                default: undefined,
            },
            userData: {
                type: Array,
                default () {
                    return [];
                },
            },
        },

        computed: {
            zxcvbn () {
                return zxcvbn(this.password || '', this.userDataCompiled);
            },
            score () {
                if (!userValidation.password.minLength(this.password)) {
                    return 0;
                }

                return this.zxcvbn.score;
            },
            passwordWarning () {
                if (!this.zxcvbn || !this.zxcvbn.feedback.warning) {
                    return undefined;
                }

                return this.zxcvbn.feedback.warning;
            },
            scoreBackgroundClasses () {
                switch (this.score) {
                    case 1 :
                        return 'w-1/4 p-1 bg-red-700';
                    case 2 :
                        return 'w-1/2 p-1 bg-yellow-600';
                    case 3 :
                        return 'w-3/4 p-1 bg-orange-700';
                    case 4 :
                        return 'w-full p-1 bg-green-600';
                }

                return 'w-1 bg-red-700';
            },
            userDataCompiled () {
                return [
                    ...this.userData,
                    ...document.title.split(/[\s|]+/),
                ].filter(Boolean);
            },
        },
    };
</script>
