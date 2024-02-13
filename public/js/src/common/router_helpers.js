import qs from 'qs';

export const scrollBehavior = function (to, from, savedPosition) {
    if (savedPosition) {
        return savedPosition;
    } else {
        return { x: 0, y: 0 };
    }
};

// set custom query resolver
export const parseQuery = function (query) {
    return qs.parse(query);
};

// set custom query stringifier
export const stringifyQuery = function (query) {
    return qs.stringify(query);
};

/*global gtag*/
export const logPageView = function (to) {
    if (window.gtag) {
        gtag('event', 'page_view', {
            page_location: window.location.origin + to.fullPath,
        });
    }
};
