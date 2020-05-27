import find from 'lodash/find';
import apolloProvider from '@/common/apollo';
import { GetTemplatesQuery } from '@/admin/queries/admin/template.query.graphql';

const state = {
    rootUrl: process.env.REQUEST_CONTEXT_SCHEME+'://'+process.env.REQUEST_CONTEXT_HOST,
    templates: null,
};

const getters = {
    templateConfig: (state) => (template) => {
        return find(state.templates, { template });
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
