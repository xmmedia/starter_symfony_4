const state = {
    mobileMenuIsOpen: false,
    // the unique ID of the subnav that's open
    subNavOpen: null,
    classes: {
        mobileOpen: 'sidebar_nav-visible',
        sideBarOpen: 'sidebar_nav-submenu-open',
    }
};

const getters = {
};

const actions = {
    openMobileMenu ({ commit, state }) {
        commit('setAdminMobileMenuStatus', true);
        document.body.classList.add(state.classes.mobileOpen);
    },
    closeMobileMenu ({ dispatch }) {
        dispatch('closeAllMenus');
    },
    subNavOpened ({ commit, state }, id) {
        commit('setAdminSubMenuOpen', id);
        document.body.classList.add(state.classes.sideBarOpen);
    },
    subNavClosed ({ commit, state }) {
        commit('setAdminSubMenuOpen', null);
        document.body.classList.remove(state.classes.sideBarOpen);
    },
    closeAllMenus ({ commit }) {
        commit('setAdminMobileMenuStatus', false);
        commit('setAdminSubMenuOpen', null);
        document.body.classList.remove(
            state.classes.mobileOpen,
            state.classes.sideBarOpen
        );
    }
};

const mutations = {
    setAdminMobileMenuStatus (state, status) {
        state.mobileMenuIsOpen = status;
    },
    setAdminSubMenuOpen (state, id) {
        state.subNavOpen = id;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}