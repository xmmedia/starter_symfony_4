<template>
    <a :href="href" class="no-underline" @click.prevent="change">
        <svg :class="[ isChecked ? 'text-green-dark' : 'text-grey-darker' ]"
             class="record_list-icon fill-current">
            <use xlink:href="#check"></use>
        </svg>
    </a>
</template>

<script>
import api from '../common/api';

export default {
    props: {
        href: {
            type: String,
            required: true,
        },
        isChecked: {
            type: Boolean,
            default: false,
        },
    },

    methods: {
        change () {
            let self = this;
            let originalVal = this.isChecked;

            this.isChecked = !this.isChecked;

            api
                .post(this.href)
                .then((response) => {
                    self.isChecked = response.data.is_checked;
                })
                .catch(() => {
                    self.isChecked = originalVal;
                });
        },
    },
};
</script>
