import Vue from 'vue';
import { ApolloClient } from 'apollo-client';
import { createHttpLink } from 'apollo-link-http';
import { InMemoryCache } from 'apollo-cache-inmemory';
import VueApollo from 'vue-apollo';
import fetch from 'unfetch';

Vue.use(VueApollo);

// HTTP connexion to the API
const httpLink = createHttpLink({
    uri: process.env.REQUEST_CONTEXT_SCHEME+'://'+process.env.REQUEST_CONTEXT_HOST+'/graphql/',
    fetch: fetch,
});

// Create the apollo client
const apolloClient = new ApolloClient({
    link: httpLink,
    // Cache implementation
    cache: new InMemoryCache(),
    defaultOptions: {
        fetchPolicy: 'cache-and-network',
    },
});

export default new VueApollo({
    defaultClient: apolloClient,
});
