import { formatNumber as libFormatPhone } from 'libphonenumber-js';
import Flatpickr from 'flatpickr';
import pluralizeFunction from 'pluralize';
import has from 'lodash/has';
import isObject from 'lodash/isObject';
import lowerCase from 'lodash/lowerCase';
import upperFirst from 'lodash/upperFirst';
import { onBeforeUnmount, watch } from 'vue';
import { onBeforeRouteLeave } from 'vue-router';

export const logError = function (e) {
    if (console && e !== undefined) {
        // eslint-disable-next-line no-console
        console.error(e);
    }
};

export const hasGraphQlError = function (e) {
    return e && e.graphQLErrors && e.graphQLErrors[0];
};

export const formatPhone = function (phone, format = 'NATIONAL') {
    // empty phone number or not an object with the required key
    if (!phone || !phone.phoneNumber) {
        return phone;
    }

    let str = phone.phoneNumber;
    if (phone.extension) {
        str += ' x'+phone.extension;
    }

    return libFormatPhone(str, 'CA', format);
};

export const date = function (_date, format = 'M j, Y') {
    if (null === _date) {
        return null;
    }

    if (!(_date instanceof Date)) {
        _date = Flatpickr.parseDate(_date, 'Y-m-d');
    }

    return Flatpickr.formatDate(_date, format);
};
export const dateTime = function (_dateTime, format ='l F j, Y \\a\\t h:iK') {
    if (null === _dateTime) {
        return null;
    }

    if (!(_dateTime instanceof Date)) {
        _dateTime = Flatpickr.parseDate(_dateTime, 'Y-m-d H:i:s');
    }

    return Flatpickr.formatDate(_dateTime, format);
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

export const hasVuelidateProp = function (v, key) {
    return has(v, key);
}

export const vuelidateValue = function (v, key) {
    if (!hasVuelidateProp(v, key)) {
        return true;
    }

    return !v[key].$invalid;
};

export const omitTypename = function (obj) {
    for (const key in obj) {
        if (isObject(obj[key])) {
            omitTypename(obj[key]);
        } else if ('__typename' === key) {
            delete obj[key];
        }
    }

    return obj;
};

export const addEditedWatcher = function (state, edited, variable) {
    watch(
        variable,
        () => {
            if (state.value.matches('ready')) {
                edited.value = true;
            }
        },
        { deep: true, flush: 'sync' },
    );
};

/**
 * Adds a leave confirmation dialog to the window and the Vue router when there are unsaved changes.
 *
 * @param {Ref} edited Must be Vue ref/reactivity object.
 * @param {Ref} isSaved Must be Vue ref/reactivity object.
 */
export const addLeaveConfirmation = (edited, isSaved) => {
    onBeforeRouteLeave((to, from, next) => {
        const msg = 'Are you sure you want to leave? You have unsaved changes.';
        if (edited.value && !isSaved.value && !confirm(msg)) {
            return next(false);
        }

        next();
    });

    const unload = (e) => {
        if (edited.value && !isSaved.value) {
            e.preventDefault();
        }
    };

    window.addEventListener('beforeunload', unload);
    onBeforeUnmount(() => {
        window.removeEventListener('beforeunload', unload);
    });
};
