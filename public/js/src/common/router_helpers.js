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
    const result = qs.stringify(query);

    return result ? ('?' + result) : '';
};

/*global ga*/
export const logPageView = function (to) {
    if (window.ga) {
        ga('set', 'page', to.fullPath);
        ga('send', 'pageview');
    }
};
