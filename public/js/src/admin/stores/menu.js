import { defineStore } from 'pinia';

export const useMenuStore = defineStore('menuStore', {
    state: () => ({
        mobileMenuIsOpen: false,
        // the unique ID of the subnav that's open
        subNavOpen: null,
        bodyClasses: {
            mobileOpen: 'sidebar_nav-visible',
            sideBarOpen: 'sidebar_nav-submenu-open',
        },
    }),

    actions: {
        openMobileMenu () {
            this.mobileMenuIsOpen = true;
            document.body.classList.add(this.bodyClasses.mobileOpen);
        },
        closeMobileMenu () {
            this.closeAllMenus();
        },
        subNavOpened (id) {
            this.subNavOpen = id;
            document.body.classList.add(this.bodyClasses.sideBarOpen);
        },
        subNavClosed () {
            this.subNavOpen = null;
            document.body.classList.remove(this.bodyClasses.sideBarOpen);
        },
        closeAllMenus () {
            this.$reset();
            document.body.classList.remove(
                this.bodyClasses.mobileOpen,
                this.bodyClasses.sideBarOpen,
            );
        },
    },
});
