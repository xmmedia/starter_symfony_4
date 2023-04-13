import { ApolloClient, InMemoryCache, ApolloLink, split, createHttpLink } from '@apollo/client/core';
import { BatchHttpLink } from '@apollo/client/link/batch-http';
import { onError } from '@apollo/client/link/error';
import { getMainDefinition } from '@apollo/client/utilities';
import { createApolloProvider } from '@vue/apollo-option'

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
export const apolloClient = new ApolloClient({
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
    // if you want to hide the message about installing apollo dev tools
    // only applicable to dev
    // connectToDevTools: false,
});

export default createApolloProvider({
    defaultClient: apolloClient,
});
