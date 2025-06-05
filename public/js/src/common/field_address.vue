<template>
    <fieldset class="mb-4 px-4 py-3 border border-gray-300">
        <legend class="px-2 text-xl"><slot>Address</slot></legend>

        <div v-if="showCountry" class="field-wrap flex-1">
            <label :for="ids.country">Country</label>
            <FieldError :v="v$.country" />
            <select v-if="localities"
                    :id="ids.country"
                    v-model="address.country"
                    autocomplete="country"
                    @change="countryChanged($event.target.value)">
                <option :value="null" disabled>– Select one –</option>
                <option v-for="country in localities.Countries"
                        :key="country.abbreviation"
                        :value="country.abbreviation">{{ country.name }}</option>
            </select>
        </div>

        <slot name="before-line1"></slot>

        <div class="field-wrap">
            <label :for="ids.line1">Line 1</label>
            <FieldError :v="v$.line1" />
            <!-- prevent enter submitting the form as enter may also be used in the suggest -->
            <input :id="ids.line1"
                   ref="inputLine1"
                   v-model="address.line1"
                   :maxlength="v$.line1.maxLength.$params.max"
                   type="text"
                   autocomplete="address-line1"
                   @keydown.enter.prevent>
        </div>

        <FieldInput v-model="address.line2"
                    :v="v$.line2"
                    autocomplete="address-line2">
            Line 2
        </FieldInput>

        <div class="flex gap-x-4">
            <div class="field-wrap flex-1">
                <label :for="ids.city">City</label>
                <FieldError :v="v$.city" />
                <!-- prevent enter submitting the form as enter may also be used in the suggest -->
                <input :id="ids.city"
                       ref="inputCity"
                       v-model="address.city"
                       :maxlength="v$.city.maxLength.$params.max"
                       type="text"
                       autocomplete="address-level2"
                       @keydown.enter.prevent>
            </div>

            <FieldInput :model-value="address.postalCode"
                        :v="v$.postalCode"
                        class="flex-1"
                        autocomplete="postal-code"
                        @update:model-value="inputPostalCode($event)">
                {{ labels.postalCode }}
            </FieldInput>
        </div>

        <div class="field-wrap">
            <label :for="ids.province">{{ labels.province }}</label>
            <FieldError :v="v$.province" />
            <select v-if="localities"
                    :id="ids.province"
                    v-model="address.province"
                    autocomplete="address-level1">
                <option :value="null" disabled>– Select one –</option>
                <option v-for="province in provinces"
                        :key="province.abbreviation"
                        :value="province.abbreviation">{{ province.name }}</option>
            </select>
        </div>
    </fieldset>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import cuid from 'cuid';
import FieldInput from './field_input';
import { LocalitiesQuery } from '@/common/queries/localities.query.graphql';
import { useQuery } from '@vue/apollo-composable';
import { Loader as MapsLoader } from '@googlemaps/js-api-loader';
import { logError } from '@/common/lib';
import { useVuelidate } from '@vuelidate/core';
import addressValidation from '@/common/validation/address';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
    showCountry: {
        type: Boolean,
        default: true,
    },
    v: {
        type: Object,
        default: null,
    },
});

const address = computed({
    get () {
        return props.modelValue;
    },
    set (value) {
        emit('update:modelValue', value);
    },
});

const ids = {
    line1: cuid(),
    city: cuid(),
    province: cuid(),
    country: cuid(),
};

const inputLine1 = ref(null);
const inputCity = ref(null);

const labels = computed(() => {
    switch (address.value.country) {
        case 'CA' :
            return {
                postalCode: 'Postal code',
                province: 'Province',
            };
        case 'US' :
            return {
                postalCode: 'Zip code',
                province: 'State',
            };
        default :
            return {
                postalCode: 'Postal/Zip code',
                province: 'Province/State',
            };
    }
});
const provinces = computed(() => {
    if (!localities.value) {
        return [];
    }

    if (!address.value.country) {
        return localities.value.Provinces;
    }

    return localities.value.Provinces.filter((province) => {
        return province.country.abbreviation === address.value.country;
    });
});

let v$;
if (props.v) {
    v$ = props.v;
} else {
    v$ = useVuelidate({
        ...addressValidation(),
    }, address);
}

const { result: localitiesResult } = useQuery(LocalitiesQuery, null, { fetchPolicy: 'cache-first' });
const localities = computed(() => {
    return localitiesResult.value;
});

function inputPostalCode (value) {
    if (typeof value === 'string') {
        value = value.toUpperCase();
        if (value.length >= 7) {
            value = value.trim();
        }
    }

    address.value.postalCode = value;
}

function countryChanged (country) {
    address.value = {
        ...address.value,
        country,
        province: null,
    };
}

const autocompletes = {
    /**
     * @type {google.maps.places.Autocomplete}
     * @link https://developers.google.com/maps/documentation/javascript/reference#Autocomplete
     */
    line1: null,
    city: null,
};

// options: https://developers.google.com/maps/documentation/javascript/load-maps-js-api#js-api-loader
new MapsLoader({
    apiKey: import.meta.env.VITE_GOOGLE_BROWSER_API_KEY,
    region: 'CA',
}).importLibrary('places').then(async ({ Autocomplete }) => {
    autocompletes.line1 = new Autocomplete(
        inputLine1.value,
        {
            types: ['geocode'],
            fields: ['address_component'],
        },
    );
    autocompletes.line1.addListener('place_changed', completeAddress);

    autocompletes.city = new Autocomplete(
        inputCity.value,
        {
            types: ['(cities)'],
            fields: ['address_component'],
        },
    );
    autocompletes.city.addListener('place_changed', completeCity);

    if (address.value.country) {
        setComponentRestrictions(address.value.country);
    }
});

watch(() => address.value.country, (country) => {
    if (country) {
        setComponentRestrictions(country);
    }
});

const setComponentRestrictions = (country) => {
    autocompletes.line1.setComponentRestrictions({ country });
    autocompletes.city.setComponentRestrictions({ country });
};

const completeAddress = () => {
    address.value = {
        ...address.value,
        line1: getAddressLine1(),
        city: getAddressComponent(['locality', 'postal_town']),
        postalCode: getAddressComponent('postal_code'),
        province: getAddressComponent('administrative_area_level_1'),
        country: props.showCountry ? getAddressComponent('country') : address.value.country,
    };
};

const completeCity = () => {
    address.value = {
        ...address.value,
        city: getAddressComponent(['locality', 'postal_town'], 'city'),
        province: getAddressComponent('administrative_area_level_1', 'city'),
        country: props.showCountry ? getAddressComponent('country') : address.value.country,
    };
};

const getAddressComponent = (types, autocompleteName = 'line1') => {
    try {
        if (!Array.isArray(types)) {
            types = [ types ];
        }

        const addressComponents = autocompletes[autocompleteName].getPlace().address_components;
        if (!addressComponents) {
            return null;
        }

        const components = addressComponents.filter((component) => {
            return types.includes(component.types[0]);
        });

        if (components.length > 0) {
            return components[0].short_name;
        } else {
            return null;
        }
    } catch (e) {
        logError(e);
        return address.value[types[0]];
    }
};

/**
 * Will pull out the street number and route (street name) and combine.
 * It also checks if the first part (up to first space) entered in the
 * line 1 field is part of the suggested address. If not, it will be
 * added as a prefix to the suggested address as it's assumed it's
 * a unit number.
 */
const getAddressLine1 = () => {
    try {
        let unitNumber = null;
        if (address.value.line1) {
            unitNumber = address.value.line1.substr(0, address.value.line1.indexOf(' '));
        }

        const addressComponents = autocompletes.line1.getPlace().address_components;

        let line1 = addressComponents.filter((component) => ([ 'street_number', 'route' ].includes(component.types[0])))
            .map((component) => component.short_name)
            .join(' ');

        if (unitNumber && !line1.startsWith(unitNumber)) {
            return unitNumber + ' ' + line1;
        }

        return line1;
    } catch (e) {
        logError(e);
        return address.value.line1;
    }
};
</script>
