import cloneDeep from 'lodash/cloneDeep';
import { email, helpers, required } from '@vuelidate/validators';
import userValidation from '@/admin/validation/user';
import { UserEmailUnique, UserPasswordValid } from '../queries/user.query.graphql';

export default {
    email: {
        required,
        email,
        unique: helpers.withAsync(async function (value) {
            if (!helpers.req(value) || !email.$validator(value)) {
                return true;
            }

            const { data: { UserEmailUnique: { unique } } } = await this.$apollo.query({
                query: UserEmailUnique,
                variables: {
                    email: value,
                },
            });

            return !!unique;
        }),
    },
    currentPassword: {
        required,
        async valid (value) {
            if (!helpers.req(value)) {
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
        ...cloneDeep(userValidation.password),
        sameAs: sameAs('repeatPassword'),
    },
    repeatPassword: {
        required,
    },
    firstName: {
        ...cloneDeep(userValidation.firstName),
    },
    lastName: {
        ...cloneDeep(userValidation.lastName),
    },
};
