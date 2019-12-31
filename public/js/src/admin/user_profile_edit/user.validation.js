import cloneDeep from 'lodash/cloneDeep';
import { email, required, sameAs } from 'vuelidate/lib/validators';
import userValidation from '@/admin/validation/user';
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
