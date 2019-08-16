import gql from 'graphql-tag';

export const CountriesQuery = gql`
    query Countries {
        Countries {
            name
            abbreviation
        },
    }
`;

export const ProvincesQuery = gql`
    query Provinces {
        Provinces {
            country {
                name
                abbreviation
              }
              name
              abbreviation
        },
    }
`;
