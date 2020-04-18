import {
    minLength,
    maxLength,
    required,
} from 'vuelidate/lib/validators';
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

            return zxcvbn(value).score > 2;
        },

        async compromised (value) {
            if (null === value || value.length < 12) {
                return true;
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
};
