import { maxLength, minLength, required } from '@vuelidate/validators';

export default () => {
    return {
        line1: {
            required,
            minLength: minLength(3),
            maxLength: maxLength(100),
        },
        line2: {
            minLength: minLength(3),
            maxLength: maxLength(100),
        },
        city: {
            required,
            minLength: minLength(2),
            maxLength: maxLength(50),
        },
        province: {
            required,
        },
        postalCode: {
            required,
            minLength: minLength(5),
            maxLength: maxLength(10),
        },
        country: {
            required,
        },
    };
};
