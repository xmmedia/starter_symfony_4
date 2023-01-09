import Vue from 'vue';
import { ApolloClient } from 'apollo-client';
import { ApolloLink, split } from 'apollo-link';
import { createHttpLink } from 'apollo-link-http';
import { BatchHttpLink } from 'apollo-link-batch-http';
import { onError } from 'apollo-link-error';
import { InMemoryCache } from 'apollo-cache-inmemory';
import { getMainDefinition } from 'apollo-utilities';
import VueApollo from 'vue-apollo';

Vue.use(VueApollo);

// docs: https://www.apollographql.com/docs/react/features/error-handling/
const errorLink = onError(({ graphQLErrors, networkError }) => {
    if (graphQLErrors) {
        graphQLErrors.map((error) => {
            // eslint-disable-next-line no-console
            console.error(
                `[GraphQL error]: Message: ${error.message}
                  Location: ${JSON.stringify(error.locations)}
                  Path: ${error.path}
                  Code: ${error.code}`,
            );
            if (error.debugMessage) {
                // eslint-disable-next-line no-console
                console.error(error.debugMessage);
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
    uri: window.location.origin+'/graphql',
});

const batchLink = new BatchHttpLink({
    uri: window.location.origin+'/graphql/batch',
});

const link = split(
    // split based on operation type
    ({ query }) => {
        const { kind, operation } = getMainDefinition(query)
        return kind === 'OperationDefinition' && operation === 'subscription'
    },
    httpLink,
    batchLink,
);

// Create the apollo client
const apolloClient = new ApolloClient({
    link: ApolloLink.from([errorLink, link]),
    // Cache implementation
    cache: new InMemoryCache(),
    defaultOptions: {
        query: {
            // docs: https://www.apollographql.com/docs/react/api/core/ApolloClient/#ApolloClient.watchQuery
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
