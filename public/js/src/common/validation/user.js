import {
    helpers,
    minLength,
    maxLength,
    required,
    email,
} from '@vuelidate/validators';
import { ref } from 'vue';
import { validPhone } from '@/common/validators';
import { pwnedPassword } from 'hibp';
import { getZxcvbn } from '@/common/zxcvbn';
import { apolloClient } from '@/common/apollo';
import { UserEmailUnique } from '@/common/queries/user.query.graphql';

export const passwordMinLength = 12;

/**
 * Passwords the server rejected with a PASSWORD_NOT_ALLOWED error. Its complexity score can differ from zxcvbn-ts's,
 * so a password that passes here can still be rejected. Add `allowed` to the password's validations
 * & pass the password to `reject()` when the mutation returns the error.
 */
export const useRejectedPasswords = () => {
    const rejectedPasswords = ref([]);

    return {
        allowed: (value) => !rejectedPasswords.value.includes(value),
        reject: (password) => {
            rejectedPasswords.value.push(password);
        },
    };
};

export default () => {
    return {
        email: {
            required,
            email,
            unique: helpers.withAsync(async function (value) {
                if (!helpers.req(value) || !email.$validator(value)) {
                    return true;
                }

                const { data: { UserEmailUnique: { unique } } } = await apolloClient.query({
                    query: UserEmailUnique,
                    variables: {
                        email: value,
                    },
                });

                return !!unique;
            }),
            $lazy: true,
        },

        password: {
            required,
            minLength: minLength(passwordMinLength),
            // this is different from the backend:
            // there's no real point other than security to the check in the backend
            maxLength: maxLength(1000),
            strength (value, user) {
                if (null === value || value.length < passwordMinLength) {
                    return true;
                }

                return getZxcvbn().check(value, [
                    user?.email,
                    user?.firstName,
                    user?.lastName,
                    ...document.title.split(/[\s|]+/),
                ].filter(Boolean)).score > 2;
            },
            compromised: helpers.withAsync(async function (value) {
                if (null === value || value.length < passwordMinLength) {
                    return true;
                }

                try {
                    // reject if in more than 3 breaches
                    return await pwnedPassword(value) < 3;
                } catch {
                    // just allow if can't connect to haveibeenpwned
                    return true;
                }
            }),
            $lazy: true,
        },
        firstName: {
            required,
            minLength: minLength(2),
            maxLength: maxLength(100),
        },
        lastName: {
            required,
            minLength: minLength(2),
            maxLength: maxLength(100),
        },
        phoneNumber: {
            valid: validPhone,
        },
    };
};
