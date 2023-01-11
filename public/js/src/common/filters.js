import lowerCase from 'lodash/lowerCase';
import upperFirst from 'lodash/upperFirst';
import { formatNumber as libFormatPhone } from 'libphonenumber-js';
import Flatpickr from 'flatpickr';
import pluralizeFunction from 'pluralize';

export const formatPhone = function (phone, format = 'NATIONAL') {
    if (!phone) {
        return;
    }

    let str = phone.phone.number();
    if (phone.extension) {
        str += ' x'+phone.extension;
    }

    return libFormatPhone(str, 'CA', format);
};

export const date = function (date, format = 'M j, Y') {
    if (null === date) {
        return null;
    }

    return Flatpickr.formatDate(Flatpickr.parseDate(date, 'Y-m-d'), format);
};

export const money = function (value, decimals = 2) {
    const price = parseFloat(value / 100);

    // from what I'm reading, Android doesn't fully support the locales and options
    try {
        return price.toLocaleString('en-CA', {
            style: 'currency',
            currency: 'CAD',
            maximumFractionDigits: decimals,
        });
    } catch (e) {
        return '$'+price.toFixed(decimals);
    }
};

export const pluralize = function (string, count) {
    return pluralizeFunction(string, count);
};

export const upperFirstFilter = function (string) {
    return upperFirst(lowerCase(string));
};
