import {
    email,
    minLength,
    maxLength,
    required,
} from 'vuelidate/lib/validators';

export default {
    email: {
        required,
        email,
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
