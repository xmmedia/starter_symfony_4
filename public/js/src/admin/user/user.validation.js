import cloneDeep from 'lodash/cloneDeep';
import has from 'lodash/has';
import { email, helpers, required, requiredIf } from '@vuelidate/validators';
import userValidation from '../validation/user';
import { GetDuplicateUsers } from '../queries/user.query.graphql';

export default {
    email: {
        required,
        email,
        unique: helpers.withAsync(async function (value) {
            if (!email.$validator(value)) {
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

            if (!has(this, 'userId')) {
                return 0 < foundUsers.length;
            }

            return 0 === foundUsers.filter(
                ({ userId }) => this.userId !== userId
            ).length;
        }),
    },
    password: {
        ...cloneDeep(userValidation.password),
        required: requiredIf(function () {
            return this.setPassword;
        }),
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
