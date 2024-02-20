import has from 'lodash/has';
import { email, helpers, required } from '@vuelidate/validators';
import { apolloClient } from '@/common/apollo';
import userValidation from '@/common/validation/user';
import { GetDuplicateUsers } from '../queries/user.query.graphql';

export default () => {
    const validations = userValidation();

    return {
        email: {
            ...validations.email,
            // replaces the unique validator from the common user validation
            unique: helpers.withAsync(async function (value) {
                if (!required.$validator(value) || !email.$validator(value)) {
                    return true;
                }

                const { data: { Users: foundUsers } } = await apolloClient.query({
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
            ...validations.password,
            required,
        },
        firstName: validations.firstName,
        lastName: validations.lastName,
        role: {
            required,
        },
        phoneNumber: validations.phoneNumber,
        // no phone number or position validation because of old data
    };
};
