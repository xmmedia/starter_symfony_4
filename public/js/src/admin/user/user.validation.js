import {
    email,
    minLength,
    maxLength,
    required,
    requiredIf,
} from 'vuelidate/lib/validators';
import { pwnedPassword } from 'hibp';
import { GetDuplicateUsers } from '../queries/user.query.graphql';

export default {
    email: {
        required,
        email,
        async unique (value) {
            if (!email(value)) {
                return true;
            }

            const { data: { Users: foundUsers } } = await this.$apollo.query({
                query: GetDuplicateUsers,
                variables: {
                    filters: {
                        emailExact: value,
                    },
                },
            });

            // something went wrong, can't do much
            if (!foundUsers) {
                return true;
            }

            if (0 === foundUsers.length) {
                return true;
            }

            if (typeof this.userId === undefined) {
                return 0 < foundUsers.length;
            }

            return 0 === foundUsers.filter(
                ({ userId }) => this.userId !== userId
            ).length;
        },
    },
    password: {
        required: requiredIf('setPassword'),
        minLength: minLength(12),
        // this is different than the backend:
        // there's no real point other than security to the check in the backend
        maxLength: maxLength(1000),
        async compromised (value) {
            if (null === value || value.length < 12) {
                return false;
            }

            // reject if in more than 3 breaches
            return await pwnedPassword(value) < 3;
        },
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
    role: {
        required,
    },
};
