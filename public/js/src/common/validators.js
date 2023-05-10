import { parsePhoneNumberFromString as parseMin } from 'libphonenumber-js';

export const validPhone = (value) => {
    if (!value) {
        return true;
    }

    const parsedPhone = parseMin(value, 'CA');

    if (!parsedPhone) {
        return false;
    }

    return parsedPhone.isValid();
};
