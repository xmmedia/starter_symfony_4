<template>
    <fieldset class="mb-4 px-4 py-3 border border-gray-300">
        <legend class="px-2 text-xl"><slot>Address</slot></legend>

        <div v-if="showCountry" class="field-wrap flex-1">
            <label :for="ids.country">Country</label>
            <FieldError :v="v.country" />
            <select v-if="localities"
                    :id="ids.country"
                    v-model="value.country"
                    autocomplete="country"
                    @change="countryChanged($event.target.value)">
                <option :value="null">– Select one –</option>
                <option v-for="country in localities.Countries"
                        :key="country.abbreviation"
                        :value="country.abbreviation">{{ country.name }}</option>
            </select>
        </div>

        <div class="field-wrap">
            <label :for="ids.line1">Line 1</label>
            <FieldError :v="v.line1" />
            <!-- prevent enter submitting the form as enter may also be used in the suggest -->
            <input :id="ids.line1"
                   ref="inputLine1"
                   v-model="value.line1"
                   :maxlength="v.line1.maxLength.$params.max"
                   type="text"
                   autocomplete="address-line1"
                   @input="input('line1', $event.target.value)"
                   @keypress.enter.prevent>
        </div>

        <FieldInput :model-value="value.line2"
                    :v="v.line2"
                    autocomplete="address-line2"
                    @update:model-value="input('line2', $event)">
            Line 2
        </FieldInput>

        <div class="flex gap-x-4">
            <div class="field-wrap flex-1">
                <label :for="ids.city">City</label>
                <FieldError :v="v.city" />
                <!-- prevent enter submitting the form as enter may also be used in the suggest -->
                <input :id="ids.city"
                       ref="inputCity"
                       v-model="value.city"
                       :maxlength="v.city.maxLength.$params.max"
                       type="text"
                       autocomplete="address-level2"
                       @input="input('city', $event.target.value)"
                       @keypress.enter.prevent>
            </div>

            <FieldInput :model-value="value.postalCode"
                        :v="v.postalCode"
                        class="flex-1"
                        autocomplete="postal-code"
                        @update:model-value="inputPostalCode($event)">
                {{ labels.postalCode }}
            </FieldInput>
        </div>

        <div class="field-wrap">
            <label :for="ids.province">{{ labels.province }}</label>
            <FieldError :v="v.province" />
            <select v-if="localities"
                    :id="ids.province"
                    v-model="value.province"
                    autocomplete="address-level1"
                    @change="input('province', $event.target.value)">
                <option :value="null">– Select one –</option>
                <option v-for="province in provinces"
                        :key="province.abbreviation"
                        :value="province.abbreviation">{{ province.name }}</option>
            </select>
        </div>
    </fieldset>
</template>

<script setup>
/*global google*/
import { computed, ref, watch } from 'vue';
import cuid from 'cuid';
import filter from 'lodash/filter';
import FieldInput from './field_input';
import { LocalitiesQuery } from '@/common/queries/localities.query.graphql';
import { useQuery } from '@vue/apollo-composable';
import { Loader as MapsLoader } from '@googlemaps/js-api-loader';
import { logError } from '@/common/lib';

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

const value = computed({
    get () {
        return props.modelValue;
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
    switch (props.modelValue.country) {
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

    if (!props.modelValue.country) {
        return localities.value.Provinces;
    }

    return localities.value.Provinces.filter((province) => {
        return province.country.abbreviation === props.modelValue.country;
    });
});

const { result: localitiesResult } = useQuery(LocalitiesQuery, null, { fetchPolicy: 'cache-first' });
const localities = computed(() => {
    return localitiesResult.value;
});

function input (field, value) {
    emit('update:modelValue', {
        ...props.modelValue,
        [field]: value,
    });
}

function inputPostalCode (value) {
    if (typeof value === 'string') {
        value = value.toUpperCase();
        if (value.length >= 7) {
            value = value.trim();
        }
    }

    input('postalCode', value);
}

function countryChanged (country) {
    emit('update:modelValue', {
        ...props.modelValue,
        country,
        province: null,
    });
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
});

watch(() => props.modelValue.country, (country) => {
    if (country) {
        autocompletes.line1.setComponentRestrictions({ country });
        autocompletes.city.setComponentRestrictions({ country });
    }
});

const completeAddress = () => {
    emit('update:modelValue', {
        ...props.modelValue,
        line1: getAddressLine1(),
        city: getAddressComponent('locality'),
        postalCode: getAddressComponent('postal_code'),
        province: getAddressComponent('administrative_area_level_1'),
        country: props.showCountry ? getAddressComponent('country') : props.modelValue.country,
    });
};

const completeCity = () => {
    emit('update:modelValue', {
        ...props.modelValue,
        city: getAddressComponent('locality', 'city'),
        province: getAddressComponent('administrative_area_level_1', 'city'),
        country: props.showCountry ? getAddressComponent('country') : props.modelValue.country,
    });
};

const getAddressComponent = (type, autocompleteName = 'line1') => {
    try {
        const addressComponents = autocompletes[autocompleteName].getPlace().address_components;
        const components = filter(addressComponents, (component) => {
            return ([ type ].indexOf(component.types[0]) > -1);
        });

        if (components.length > 0) {
            return components[0].short_name;
        } else {
            return null;
        }
    } catch (e) {
        logError(e);
        return props.modelValue[type];
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
        const addressComponents = autocompletes.line1.getPlace().address_components;

        return filter(addressComponents, (component) => ([ 'street_number', 'route' ].includes(component.types[0])))
            .map((component) => component.short_name)
            .join(' ');
    } catch (e) {
        logError(e);
        return props.modelValue.line1;
    }
};
</script>
