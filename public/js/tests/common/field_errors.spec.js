'use strict';
import { shallowMount } from '@vue/test-utils';
import fieldErrors from '@/common/field_errors.vue';

describe('common/field_errors.vue', () => {
    it('can mount', () => {
        const wrapper = shallowMount(fieldErrors);

        expect(wrapper.find('.field-errors').exists()).toBe(false);
    });

    it('can mount with error array', () => {
        const wrapper = shallowMount(fieldErrors, {
            propsData: {
                errors: ["Message 1", "Message 2"],
            },
        });

        expect(wrapper.findAll('.field-errors').length).toBe(1);
        expect(wrapper.findAll('.field-errors li').length).toBe(2);
    });

    it('can mount with error object', () => {
        const wrapper = shallowMount(fieldErrors, {
            propsData: {
                errors: {key1: "Message 1", key2: "Message 2"},
            },
        });

        expect(wrapper.findAll('.field-errors').length).toBe(1);
        expect(wrapper.findAll('.field-errors li').length).toBe(2);
    });
});
