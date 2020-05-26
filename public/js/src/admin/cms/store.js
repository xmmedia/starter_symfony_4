import apolloProvider from '@/common/apollo';
import { GetTemplatesQuery } from '@/admin/queries/template.query.graphql';

const state = {
    templates: null,
};

const getters = {
    defaultTemplate (state) {
        if (!state.templates) {
            return null;
        }

        return state.templates.filter((template) => template.default)[0].template;
    },
};

const actions = {
    async loadTemplates ({ commit }) {
        const { data: { Templates } } = await apolloProvider.defaultClient.query(
            { query: GetTemplatesQuery }
        );

        const templates = Templates.map((template) => {
            return {
                ...template,
                items: template.items.map((item) => {
                    return {
                        ...item,
                        config: JSON.parse(item.config),
                    };
                }),
            };
        });

        commit('setTemplates', templates);
    },
};

const mutations = {
    setTemplates (state, templates) {
        state.templates = templates;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};
