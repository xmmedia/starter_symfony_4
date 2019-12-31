import cloneDeep from 'lodash/cloneDeep';
import { email, required, requiredIf } from 'vuelidate/lib/validators';
import userValidation from '../validation/user';
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
        ...cloneDeep(userValidation.password),
        required: requiredIf('setPassword'),
    },
    firstName: {
        ...cloneDeep(userValidation.firstName),
    },
    lastName: {
        ...cloneDeep(userValidation.lastName),
    },
    role: {
        required,
    },
};
