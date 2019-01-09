import ApolloClient from 'apollo-boost';
import VueApollo from 'vue-apollo';

const apolloClient = new ApolloClient({
    uri: 'https://dev.example.com/graphql',
});
const apolloProvider = new VueApollo({
    defaultClient: apolloClient,
});

export default apolloProvider;
