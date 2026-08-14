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

        <div class="field-wrap relative">
            <label :for="ids.line1">Line 1</label>
            <FieldError :v="v$.line1" />
            <input :id="ids.line1"
                   v-model="address.line1"
                   v-bind="line1InputAttrs"
                   :maxlength="v$.line1.maxLength.$params.max"
                   type="text"
                   autocomplete="address-line1">
            <PlaceSuggestions :list-id="line1ListId"
                              :suggestions="line1Suggestions"
                              :active-index="line1ActiveIndex"
                              @select="selectLine1($event)"
                              @activate="line1ActiveIndex = $event" />
        </div>

        <FieldInput v-model="address.line2"
                    :v="v$.line2"
                    autocomplete="address-line2">
            Line 2
        </FieldInput>

        <div class="flex gap-x-4">
            <div class="field-wrap flex-1 relative">
                <label :for="ids.city">City</label>
                <FieldError :v="v$.city" />
                <input :id="ids.city"
                       v-model="address.city"
                       v-bind="cityInputAttrs"
                       :maxlength="v$.city.maxLength.$params.max"
                       type="text"
                       autocomplete="address-level2">
                <PlaceSuggestions :list-id="cityListId"
                                  :suggestions="citySuggestions"
                                  :active-index="cityActiveIndex"
                                  @select="selectCity($event)"
                                  @activate="cityActiveIndex = $event" />
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
import { computed, watch } from 'vue';
import { createId } from '@paralleldrive/cuid2';
import FieldInput from './field_input.vue';
import PlaceSuggestions from './place_suggestions.vue';
import { LocalitiesQuery } from '@/common/queries/localities.query.graphql';
import { useQuery } from '@vue/apollo-composable';
import { usePlaceAutocomplete } from '@/common/place_autocomplete';
import { logError } from '@/common/lib';
import { useVuelidate } from '@vuelidate/core';
import addressValidation from '@/common/validation/address';

const address = defineModel({ type: Object });

const props = defineProps({
    showCountry: {
        type: Boolean,
        default: true,
    },
    v: {
        type: Object,
        default: null,
    },
});

const ids = {
    line1: createId(),
    city: createId(),
    province: createId(),
    country: createId(),
};

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

const inputPostalCode = (value) => {
    if (typeof value === 'string') {
        value = value.toUpperCase();
        if (value.length >= 7) {
            value = value.trim();
        }
    }

    address.value.postalCode = value;
};

const countryChanged = (country) => {
    address.value = {
        ...address.value,
        country,
        province: null,
    };
};

const completeAddress = (components) => {
    address.value = {
        ...address.value,
        line1: getAddressLine1(components),
        city: getAddressComponent(components, [ 'locality', 'postal_town' ], 'city'),
        postalCode: getAddressComponent(components, 'postal_code', 'postalCode'),
        province: getAddressComponent(components, 'administrative_area_level_1', 'province'),
        country: props.showCountry
            ? getAddressComponent(components, 'country', 'country')
            : address.value.country,
    };
};

const completeCity = (components) => {
    address.value = {
        ...address.value,
        city: getAddressComponent(components, [ 'locality', 'postal_town' ], 'city'),
        province: getAddressComponent(components, 'administrative_area_level_1', 'province'),
        country: props.showCountry
            ? getAddressComponent(components, 'country', 'country')
            : address.value.country,
    };
};

const {
    listId: line1ListId,
    suggestions: line1Suggestions,
    activeIndex: line1ActiveIndex,
    inputAttrs: line1InputAttrs,
    setRegionCode: setLine1RegionCode,
    select: selectLine1,
} = usePlaceAutocomplete({
    includedPrimaryTypes: [ 'street_address', 'premise', 'subpremise', 'route' ],
    onSelect: completeAddress,
});

const {
    listId: cityListId,
    suggestions: citySuggestions,
    activeIndex: cityActiveIndex,
    inputAttrs: cityInputAttrs,
    setRegionCode: setCityRegionCode,
    select: selectCity,
} = usePlaceAutocomplete({
    includedPrimaryTypes: [ '(cities)' ],
    onSelect: completeCity,
});

watch(() => address.value.country, (country) => {
    setLine1RegionCode(country);
    setCityRegionCode(country);
}, { immediate: true });

/**
 * @param {Array} components The place's address components: { longText, shortText, types }.
 * @param {string|string[]} types The component type(s) to pull the value from.
 * @param {string} addressField The address field to fall back to when the component isn't found.
 */
const getAddressComponent = (components, types, addressField) => {
    try {
        if (!Array.isArray(types)) {
            types = [ types ];
        }

        const component = components.find((component) => types.includes(component.types[0]));

        return component ? component.shortText : null;
    } catch (e) {
        logError(e);

        return address.value[addressField];
    }
};

/**
 * Will pull out the street number and route (street name) and combine.
 * It also checks if the first part (up to first space) entered in the
 * line 1 field is part of the suggested address. If not, it will be
 * added as a prefix to the suggested address as it's assumed it's
 * a unit number.
 */
const getAddressLine1 = (components) => {
    try {
        let unitNumber = null;
        if (address.value.line1) {
            unitNumber = address.value.line1.substr(0, address.value.line1.indexOf(' '));
        }

        const line1 = components
            .filter((component) => [ 'street_number', 'route' ].includes(component.types[0]))
            .map((component) => component.shortText)
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
