<template>
    <fieldset class="mb-4 px-4 py-3 border border-gray-300">
        <legend class="px-2 text-xl"><slot>Address</slot></legend>

        <div v-if="showCountry" class="field-wrap flex-1">
            <label :for="ids.country">Country</label>
            <FieldError :v="v.country" />
            <select v-if="localities"
                    v-model="value.country"
                    :id="ids.country"
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

        <FieldInput v-model="value.line2"
                    :v="v.line2"
                    autocomplete="address-line2"
                    @update:modelValue="input('line2', $event)">
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

            <FieldInput v-model="value.postalCode"
                        :v="v.postalCode"
                        class="flex-1"
                        autocomplete="postal-code"
                        @input="input('postalCode', $event)">
                {{ labels.postalCode }}
            </FieldInput>
        </div>

        <div class="field-wrap">
            <label :for="ids.province">{{ labels.province }}</label>
            <FieldError :v="v.province" />
            <select v-if="localities"
                    v-model="value.province"
                    :id="ids.province"
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
import { computed, onMounted, ref } from 'vue';
import cuid from 'cuid';
import filter from 'lodash/filter';
import FieldInput from './field_input';
import { LocalitiesQuery } from '@/admin/queries/localities.query.graphql';
import { useQuery } from '@vue/apollo-composable';

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

/**
 * @type {Autocomplete}
 * @link https://developers.google.com/maps/documentation/javascript/reference#Autocomplete
 */
const autocomplete = {
    address: null,
    city: null,
};
const autocompleteOptions = {
    address: {
        types: ['geocode'],
        fields: ['address_component'],
        componentRestrictions: { country: props.modelValue.country },
    },
    city: {
        types: ['(cities)'],
        fields: ['address_component'],
        componentRestrictions: { country: props.modelValue.country },
    },
};
let autocompleteTimeoutCount = 0;

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

onMounted(() => {
    startAutocompleteLoadTimer();
});

function input (field, value) {
    emit('update:modelValue', {
        ...props.modelValue,
        [field]: value,
    });
}

function countryChanged (country) {
    emit('update:modelValue', {
        ...props.modelValue,
        country,
        province: null,
    });
}

function setupAutocomplete () {
    ++autocompleteTimeoutCount;

    if (google?.maps?.places) {
        autocomplete.address = new google.maps.places.Autocomplete(
            inputLine1.value,
            autocompleteOptions.address,
        );
        autocomplete.address.addListener('place_changed', completeAddress);

        autocomplete.city = new google.maps.places.Autocomplete(
            inputCity.value,
            autocompleteOptions.city,
        );
        autocomplete.city.addListener('place_changed', completeCity);

    } else if (autocompleteTimeoutCount < 60) {
        // retry 60 times so ~120 seconds
        startAutocompleteLoadTimer();
    }
}
function startAutocompleteLoadTimer () {
    setTimeout(setupAutocomplete, autocompleteTimeoutCount > 2 ? 2000 : 500);
}

function completeAddress () {
    emit('update:modelValue', {
        ...props.modelValue,
        line1: getAddressLine1(),
        city: getAddressComponent('locality'),
        postalCode: getAddressComponent('postal_code'),
        province: getAddressComponent('administrative_area_level_1'),
        country: props.showCountry ? getAddressComponent('country') : props.modelValue.country,
    });
}

function completeCity () {
    emit('update:modelValue', {
        ...props.modelValue,
        city: getAddressComponent('locality', 'city'),
        province: getAddressComponent('administrative_area_level_1', 'city'),
        country: props.showCountry ? getAddressComponent('country') : props.modelValue.country,
    });
}

function getAddressComponent (type, autocompleteName = 'address') {
    const addressComponents = autocomplete[autocompleteName].getPlace().address_components;
    const components = filter(addressComponents, (component) => {
        return ([type].indexOf(component.types[0]) > -1);
    });

    if (components.length > 0) {
        return components[0].short_name;
    } else {
        return null;
    }
}

/**
 * Will pull out the street number and route (street name) and combine.
 * It also checks if the first part (up to first space) entered in the
 * line 1 field is part of the suggested address. If not, it will be
 * added as a prefix to the suggested address as it's assumed it's
 * a unit number.
 */
function getAddressLine1 () {
    const addressComponents = autocomplete.address.getPlace().address_components;

    return filter(addressComponents, (component) => {
        return (['street_number', 'route'].indexOf(component.types[0]) > -1);
    })
        .map((component) => {
            return component.short_name;
        })
        .join(' ');
}
</script>
