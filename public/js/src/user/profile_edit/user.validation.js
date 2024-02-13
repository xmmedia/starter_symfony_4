import { helpers, required } from '@vuelidate/validators';
import { apolloClient } from '@/common/apollo';
import userValidation from '@/common/validation/user';
import { UserPasswordValid } from '../../user/queries/user.query.graphql';

export default (userData = []) => {
    const validations = userValidation(userData);

    return {
        email: validations.email,
        currentPassword: {
            required,
            valid: helpers.withAsync(async function (value) {
                if (!helpers.req(value)) {
                    return true;
                }

                // true when correct, false when incorrect
                const { data: { UserPasswordValid: { valid } } } = await apolloClient.query({
                    query: UserPasswordValid,
                    variables: {
                        password: value,
                    },
                });

                return !!valid;
            }),
            $lazy: true,
        },
        newPassword: validations.password,
        repeatPassword: {
            required,
        },
        firstName: validations.firstName,
        lastName: validations.lastName,
        phoneNumber: validations.phoneNumber,
    };
};
