import {
    helpers,
    minLength,
    maxLength,
    required,
} from '@vuelidate/validators';
import store from '@/admin/store';
import { pwnedPassword } from 'hibp';
import zxcvbn from 'zxcvbn';

export default {
    password: {
        required,
        minLength: minLength(12),
        // this is different than the backend:
        // there's no real point other than security to the check in the backend
        maxLength: maxLength(1000),
        strength (value) {
            if (null === value || value.length < 8) {
                return true;
            }

            const userData = [
                // deal with the values not existing for example when they're using the forgot password
                store.state.user?.firstName || null,
                store.state.user?.lastName || null,
                store.state.user?.email || null,
                ...document.title.split(/[\s|]+/),
            ];

            return zxcvbn(value, userData).score > 2;
        },

        compromised: helpers.withAsync(async function (value) {
            if (null === value || value.length < 12) {
                return true;
            }

            // reject if in more than 3 breaches
            return await pwnedPassword(value) < 3;
        }),
        $lazy: true,
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
