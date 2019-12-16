import {
    email,
    minLength,
    maxLength,
    required,
    sameAs,
} from 'vuelidate/lib/validators';
import { pwnedPassword } from 'hibp';
import { UserEmailUnique, UserPasswordValid } from '../queries/user.query.graphql';

export default {
    email: {
        required,
        email,
        async unique (value) {
            if (!email(value)) {
                return true;
            }

            const { data: { UserEmailUnique: { unique } } } = await this.$apollo.query({
                query: UserEmailUnique,
                variables: {
                    email: value,
                },
            });

            return !!unique;
        },
    },
    currentPassword: {
        required,
        async valid (value) {
            if (!required(value)) {
                return true;
            }

            const { data: { UserPasswordValid: { valid } } } = await this.$apollo.query({
                query: UserPasswordValid,
                variables: {
                    password: value,
                },
            });

            return !!valid;
        },
    },
    newPassword: {
        required,
        minLength: minLength(12),
        // this is different than the backend:
        // there's no real point other than security to the check in the backend
        maxLength: maxLength(1000),
        sameAs: sameAs('repeatPassword'),
        async compromised (value) {
            if (null === value || value.length < 12) {
                return true;
            }

            // reject if in more than 3 breaches
            return await pwnedPassword(value) < 3;
        },
    },
    repeatPassword: {
        required,
    },
    firstName: {
        required,
        minLength: minLength(2),
        maxLength: maxLength(50),
    },
    lastName: {
        required,
        minLength: minLength(2),
        maxLength: maxLength(50),
    },
};
