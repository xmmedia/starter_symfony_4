import {
    helpers,
    minLength,
    maxLength,
    required,
    email,
} from '@vuelidate/validators';
import { validPhone } from '@/common/validators';
import { pwnedPassword } from 'hibp';
import zxcvbn from 'zxcvbn';
import { apolloClient } from '@/common/apollo';
import { UserEmailUnique } from '@/common/queries/user.query.graphql';

export const passwordMinLength = 12;

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

                return zxcvbn(value, [
                    user?.email,
                    user?.firstName,
                    user?.lastName,
                    ...document.title.split(/[\s|]+/),
                ]).score > 2;
            },
            compromised: helpers.withAsync(async function (value) {
                if (null === value || value.length < passwordMinLength) {
                    return true;
                }

                try {
                    // reject if in more than 3 breaches
                    return await pwnedPassword(value) < 3;
                } catch (e) {
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
