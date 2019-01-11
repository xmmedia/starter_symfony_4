import { ApolloClient } from 'apollo-client';
import { createHttpLink } from 'apollo-link-http';
import { setContext } from 'apollo-link-context';
import { InMemoryCache } from 'apollo-cache-inmemory';
import VueApollo from 'vue-apollo';

// HTTP connexion to the API
const httpLink = createHttpLink({
    uri: process.env.REQUEST_CONTEXT_SCHEME+'://'+process.env.REQUEST_CONTEXT_HOST+'/graphql/',
    connectToDevTools: process.env.NODE_ENV === 'production',
});

const csrfLink = setContext((_, { headers }) => {
    const value = "; " + document.cookie;
    const parts = value.split("; CSRF-TOKEN=");

    // couldn't find the cookie
    if (parts.length !== 2) {
        return {
            headers: { ...headers },
        };
    }

    const token = parts.pop().split(";").shift();

    // return the headers to the context so httpLink can read them
    return {
        headers: {
            ...headers,
            'X-CSRF-TOKEN': token,
        },
    };
});

// Create the apollo client
const apolloClient = new ApolloClient({
    link: csrfLink.concat(httpLink),
    cache: new InMemoryCache(),
});

const apolloProvider = new VueApollo({
    defaultClient: apolloClient,
});

export default apolloProvider;
