<template>
    <div class="field-wrap">
        <label :for="id">Role</label>

        <field-error v-if="v.$error">
            <template v-if="!v.required">
                A Role is required.
            </template>
        </field-error>

        <select :id="id"
                :value="value"
                @change="$emit('input', $event.target.value)">
            <option v-for="(name,role) in availableRoles"
                    :key="role"
                    :value="role">{{ name }}</option>
        </select>
    </div>
</template>

<script>
import cuid from 'cuid';
import { mapState } from 'vuex';

export default {
    props: {
        value: {
            type: String,
            default: null,
        },
        v: {
            type: Object,
            required: true,
        },
    },

    data () {
        return {
            id: cuid(),
        };
    },

    computed: {
        ...mapState([
            'availableRoles',
        ]),
    },
}
</script>
