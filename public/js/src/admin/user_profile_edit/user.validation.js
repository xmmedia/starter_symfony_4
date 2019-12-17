import cloneDeep from 'lodash/cloneDeep';
import {
    email,
    minLength,
    maxLength,
    required,
} from 'vuelidate/lib/validators';
import userValidation from '@/common/validation/user';
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
