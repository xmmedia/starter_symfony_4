import Vue from 'vue';
import { ApolloClient } from 'apollo-client';
import { ApolloLink } from 'apollo-link';
import { createHttpLink } from 'apollo-link-http';
import { onError } from 'apollo-link-error';
import { InMemoryCache } from 'apollo-cache-inmemory';
import VueApollo from 'vue-apollo';
import fetch from 'unfetch';
import router from '@/admin/router';

Vue.use(VueApollo);

// docs: https://www.apollographql.com/docs/react/features/error-handling/
const errorLink = onError(({ graphQLErrors, networkError }) => {
    if (graphQLErrors) {
        graphQLErrors.map((error) => {
            // eslint-disable-next-line no-console
            console.error(
                `[GraphQL error]: Message: ${error.message}
                  Location: ${JSON.stringify(error.locations)}
                  Path: ${error.path}`,
            );
            if (error.debugMessage) {
                // eslint-disable-next-line no-console
                console.error(error.debugMessage);
            }
            if (401 === error.code) {
                window.location = router.resolve({ name: 'login' }).href;
            }
        });
    }
    if (networkError) {
        // eslint-disable-next-line no-console
        console.error(`[Network error]: ${networkError}`);
    }
});

// http link
const httpLink = createHttpLink({
    uri: process.env.REQUEST_CONTEXT_SCHEME+'://'+process.env.REQUEST_CONTEXT_HOST+'/graphql/',
    fetch,
});

// Create the apollo client
const apolloClient = new ApolloClient({
    link: ApolloLink.from([errorLink, httpLink]),
    // Cache implementation
    cache: new InMemoryCache(),
    defaultOptions: {
        query: {
            // docs: https://www.apollographql.com/docs/react/api/react-apollo/#optionsfetchpolicy
            fetchPolicy: 'no-cache',
        },
        watchQuery: {
            fetchPolicy: 'no-cache',
        },
    },
});

export default new VueApollo({
    defaultClient: apolloClient,
});
