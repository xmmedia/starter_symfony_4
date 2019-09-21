import {
    email,
    minLength,
    maxLength,
    required,
    requiredIf,
} from 'vuelidate/lib/validators';
import { GetUsersQuery } from '../queries/user.query.graphql';

export default {
    email: {
        required,
        email,
        // @todo deal with existing user
        async duplicate (value) {
            if (email(value)) {
                return true;
            }

            const { data } = await this.$apollo.query({
                query: GetUsersQuery,
                variables () {
                    return {
                        filters: {
                            emailExact: value,
                        },
                    };
                },
                fetchPolicy: 'no-cache',
            });

            return data.Users && data.Users.length > 0;
        },
    },
    password: {
        required: requiredIf('setPassword'),
        minLength: minLength(12),
        // this is different than the backend:
        // there's no real point other than security to the check in the backend
        maxLength: maxLength(1000),
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
