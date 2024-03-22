import { ApolloClient, InMemoryCache, ApolloLink, split } from '@apollo/client/core';
import { BatchHttpLink } from '@apollo/client/link/batch-http';
import createUploadLink from 'apollo-upload-client/createUploadLink.mjs';
import extractFiles from 'extract-files/extractFiles.mjs';
import isExtractableFile from 'extract-files/isExtractableFile.mjs';
import { onError } from '@apollo/client/link/error';

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

const link = split(
    // split based if there's an upload
    operation => extractFiles(operation, isExtractableFile).files.size > 0,
    createUploadLink({
        uri: window.location.origin+'/graphql',
    }),
    new BatchHttpLink({
        uri: window.location.origin+'/graphql/batch',
    }),
);

// Create the apollo client
export const apolloClient = new ApolloClient({
    link: ApolloLink.from([errorLink, link]),
    // Cache implementation
    cache: new InMemoryCache(),
    defaultOptions: {
        query: {
            // options: https://www.apollographql.com/docs/react/data/queries/#supported-fetch-policies
            // docs: https://www.apollographql.com/docs/react/api/core/ApolloClient/#ApolloClient.watchQuery
            fetchPolicy: 'no-cache',
        },
        watchQuery: {
            fetchPolicy: 'no-cache',
        },
    },
    // if you want to hide the message about installing apollo dev tools
    // only applicable to dev
    connectToDevTools: false,
});
