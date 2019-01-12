import { ApolloClient } from 'apollo-client';
import { HttpLink } from 'apollo-link-http';
import { InMemoryCache } from 'apollo-cache-inmemory';
import VueApollo from 'vue-apollo';

// HTTP connexion to the API
const httpLink = new HttpLink({
    uri: process.env.REQUEST_CONTEXT_SCHEME+'://'+process.env.REQUEST_CONTEXT_HOST+'/graphql/',
    connectToDevTools: process.env.NODE_ENV === 'production',
});

// Cache implementation
const cache = new InMemoryCache();

// Create the apollo client
const apolloClient = new ApolloClient({
    link: httpLink,
    cache,
});

const apolloProvider = new VueApollo({
    defaultClient: apolloClient,
});

export default apolloProvider;
