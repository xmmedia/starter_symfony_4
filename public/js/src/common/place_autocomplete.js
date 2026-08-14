import { computed, ref, shallowRef } from 'vue';
import { createId } from '@paralleldrive/cuid2';
import { importLibrary, setOptions } from '@googlemaps/js-api-loader';
import { logError } from '@/common/lib';

// options: https://developers.google.com/maps/documentation/javascript/load-maps-js-api#js-api-loader
setOptions({
    key: import.meta.env.VITE_GOOGLE_BROWSER_API_KEY,
    region: 'CA',
});

const MIN_INPUT_LENGTH = 3;
const DEBOUNCE_MS = 200;

let placesLibrary = null;
const loadPlaces = () => {
    placesLibrary ??= importLibrary('places');

    return placesLibrary;
};

/**
 * Address suggestions built on the Places Autocomplete Data API, the replacement
 * for the deprecated google.maps.places.Autocomplete widget.
 *
 * @link https://developers.google.com/maps/documentation/javascript/place-autocomplete-data
 *
 * @param {string[]} includedPrimaryTypes Place types to restrict the suggestions to.
 * @param {function} onSelect Called with the selected place's address components:
 *                            an array of { longText, shortText, types }.
 */
export const usePlaceAutocomplete = ({ includedPrimaryTypes, onSelect }) => {
    const listId = createId();
    const suggestions = shallowRef([]);
    const activeIndex = ref(-1);

    let regionCode = null;
    // groups the searches & the selection into one billable session
    let sessionToken = null;
    // so a slow response can't overwrite the suggestions for a newer search
    let latestSearch = 0;
    let debounceTimer = null;

    const setRegionCode = (country) => {
        regionCode = country;
    };

    const close = () => {
        clearTimeout(debounceTimer);
        suggestions.value = [];
        activeIndex.value = -1;
    };

    const fetchSuggestions = async (input) => {
        const search = ++latestSearch;

        try {
            const { AutocompleteSessionToken, AutocompleteSuggestion } = await loadPlaces();

            sessionToken ??= new AutocompleteSessionToken();

            const request = {
                input,
                includedPrimaryTypes,
                sessionToken,
            };
            if (regionCode) {
                request.includedRegionCodes = [ regionCode ];
            }

            const { suggestions: results } = await AutocompleteSuggestion.fetchAutocompleteSuggestions(request);

            if (search !== latestSearch) {
                return;
            }

            suggestions.value = results;
            activeIndex.value = -1;
        } catch (e) {
            logError(e);
            close();
        }
    };

    const search = (input) => {
        clearTimeout(debounceTimer);

        if (!input || input.trim().length < MIN_INPUT_LENGTH) {
            close();

            return;
        }

        debounceTimer = setTimeout(() => fetchSuggestions(input.trim()), DEBOUNCE_MS);
    };

    const select = async (suggestion) => {
        close();

        try {
            const place = suggestion.placePrediction.toPlace();
            await place.fetchFields({ fields: [ 'addressComponents' ]});
            // fetchFields ends the session: the next search starts a new one
            sessionToken = null;

            onSelect(place.addressComponents ?? []);
        } catch (e) {
            logError(e);
        }
    };

    const move = (offset) => {
        if (0 === suggestions.value.length) {
            return;
        }

        activeIndex.value = (activeIndex.value + offset + suggestions.value.length) % suggestions.value.length;
    };

    const keydown = (e) => {
        switch (e.key) {
            case 'ArrowDown' :
                e.preventDefault();
                move(1);
                break;
            case 'ArrowUp' :
                e.preventDefault();
                move(-1);
                break;
            case 'Enter' :
                // always prevented so enter in the suggest doesn't submit the form
                e.preventDefault();
                if (activeIndex.value > -1) {
                    select(suggestions.value[activeIndex.value]);
                }
                break;
            case 'Escape' :
            case 'Tab' :
                close();
                break;
        }
    };

    // bound onto the input with v-bind
    const inputAttrs = computed(() => ({
        role: 'combobox',
        'aria-autocomplete': 'list',
        'aria-controls': listId,
        'aria-expanded': suggestions.value.length > 0,
        'aria-activedescendant': activeIndex.value > -1 ? `${listId}-${activeIndex.value}` : null,
        onInput: (e) => search(e.target.value),
        onKeydown: keydown,
        onBlur: close,
    }));

    return {
        listId,
        suggestions,
        activeIndex,
        inputAttrs,
        setRegionCode,
        select,
        close,
    };
};
