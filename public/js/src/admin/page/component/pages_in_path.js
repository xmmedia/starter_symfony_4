export const pagesInPath = function (pages, path) {
    const re = new RegExp("^" + path.replace("/", "\\/") + "\\/[^\\/]*$");

    return pages.filter((page) => re.test(page.path));
};
