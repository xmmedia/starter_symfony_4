'use strict';
import Vue from 'vue';
import fieldErrors from '../../src/common/field_errors.vue';
import escape from 'escape-html';

describe('common/fieldErrors', () => {
    it('can mount', (done) => {
        const vm = new Vue({
            template: '<div><fieldErrors></fieldErrors></div>',
            components: {
                fieldErrors
            }
        }).$mount();

        Vue.nextTick()
            .then(() => {
                assert.equal(vm.$el.querySelectorAll('.field-errors').length, 0);
                done();
            })
            .catch(done);
    });

    it('can mount with error array', (done) => {
        const errors = JSON.stringify(["Message 1", "Message 2"]);

        const vm = new Vue({
            template: '<div><fieldErrors :errors="'+escape(errors)+'"></fieldErrors></div>',
            components: {
                fieldErrors
            }
        }).$mount();

        Vue.nextTick()
            .then(() => {
                assert.equal(vm.$el.querySelectorAll('.field-errors').length, 1);
                assert.equal(vm.$el.querySelectorAll('.field-errors li').length, 2);
                done();
            })
            .catch(done);
    });

    it('can mount with error object', (done) => {
        const errors = JSON.stringify({key1: "Message 1", key2: "Message 2"});

        const vm = new Vue({
            template: '<div><fieldErrors :errors="'+escape(errors)+'"></fieldErrors></div>',
            components: {
                fieldErrors
            }
        }).$mount();

        Vue.nextTick()
            .then(() => {
                assert.equal(vm.$el.querySelectorAll('.field-errors').length, 1);
                assert.equal(vm.$el.querySelectorAll('.field-errors li').length, 2);
                done();
            })
            .catch(done);
    });
});