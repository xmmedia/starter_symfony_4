<template>
    <div class="field-wrap">
        <label for="inputRole">Role</label>
        <field-errors :errors="validationErrors" field="role" />
        <select id="inputRole"
                :value="value"
                @change="$emit('input', $event.target.value)">
            <option v-for="(name,role) in availableRoles"
                    :key="role"
                    :value="role">{{ name }}</option>
        </select>
    </div>
</template>

<script>
import { mapState } from 'vuex';

export default {
    props: {
        value: {
            type: String,
            default: null,
        },
        validationErrors: {
            type: Object,
            default: function () {
                return {};
            },
        },
    },

    computed: {
        ...mapState([
            'availableRoles',
        ]),
    },
}
</script>
