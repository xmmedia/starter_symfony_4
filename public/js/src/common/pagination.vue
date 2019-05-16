<template>
    <div v-if="pageCount > 1" class="my-4 mx-auto text-center clearfix">
        <global-events @keydown.left="goToPrevious" @keydown.right="goToNext" />

        <router-link v-if="current !== 1"
                     :to="{ name: routeName, query: { offset: 0 } }"
                     :class="linkClasses"
                     class="inline-block">&lt;&lt;</router-link>
        <span v-else class="inline-block w-12 p-1 text-grey-darker">&lt;&lt;</span>

        <router-link v-if="previous !== null"
                     :to="previousRoute"
                     :class="linkClasses"
                     class="inline-block">&lt;</router-link>
        <span v-else :class="spanClasses" class="inline-block">&lt;</span>

        <span v-if="showBeforeEllipsis"
              class="hidden lg:inline-block w-4 p-1 text-grey-darker">…</span>

        <template v-for="page in pagesInRange">
            <router-link v-if="page !== current"
                         :to="{ name: routeName, query: { offset: (page - 1) * itemsPerPage } }"
                         :key="page"
                         :class="linkClasses" class="hidden lg:inline-block">{{ page }}</router-link>
            <span v-else
                  :key="page"
                  :class="spanClasses"
                  class="hidden lg:inline-block border border-grey-light rounded">{{ page }}</span>
        </template>

        <span v-if="showAfterEllipsis"
              class="hidden lg:inline-block w-4 p-1 text-grey-darker">…</span>

        <router-link v-if="next !== null"
                     :to="nextRoute"
                     :class="linkClasses"
                     class="inline-block">&gt;</router-link>
        <span v-else :class="spanClasses" class="inline-block">&gt;</span>

        <router-link v-if="offset !== last"
                     :to="{ name: routeName, query: { offset: last } }"
                     :class="linkClasses"
                     class="inline-block">&gt;&gt;</router-link>
        <span v-else class="inline-block w-12 p-1 text-grey-darker">&gt;&gt;</span>
    </div>
</template>

<script>
import range from 'lodash/range';
import GlobalEvents from 'vue-global-events';

export default {
    components: {
        GlobalEvents,
    },

    props: {
        routeName: {
            type: String,
            required: true,
        },
        count: {
            type: Number,
            required: true,
        },
        itemsPerPage: {
            type: Number,
            default: 30,
        },
        /**
         * The offset, not the page number.
         */
        offset: {
            type: Number,
            default: 0,
        },
        pageRange: {
            type: Number,
            default: 5,
        },
    },

    data () {
        return {
            linkClasses: 'w-12 p-1 hover:no-underline hover:bg-blue-lightest',
            spanClasses: 'w-12 p-1 text-grey-darker',
        };
    },

    computed: {
        /**
         * Current page number.
         */
        current () {
            if (this.offset === 0) {
                return 1;
            }
            if (this.offset > this.count) {
                return this.last / this.itemsPerPage;
            }

            return Math.ceil(this.offset / this.itemsPerPage) + 1;
        },

        pageCount () {
            return Math.ceil(this.count / this.itemsPerPage);
        },

        /**
         * Previous page number.
         */
        previous () {
            if (this.current > 1) {
                return this.current - 1;
            }

            return null;
        },

        /**
         * Next page number.
         */
        next () {
            if (this.current < this.pageCount) {
                return this.current + 1;
            }

            return null;
        },

        /**
         * Last page number.
         */
        last () {
            return (this.pageCount - 1) * this.itemsPerPage;
        },

        rangeDelta () {
            return Math.ceil(this.pageRange / 2);
        },

        /**
         * Array of page numbers.
         */
        pagesInRange () {
            let delta = this.rangeDelta;

            // if at the end of the range
            if (this.current - delta > this.pageCount - this.pageRange) {
                let start = this.pageCount - this.pageRange + 1;
                if (start < 1) {
                    start = 1;
                }

                // range doesn't include the end number
                return range(start, this.pageCount + 1);
            }

            if (this.current - delta < 0) {
                delta = this.current;
            }

            const start = this.current - delta + 1;

            // if anywhere before the end
            return range(start, start + this.pageRange);
        },

        showBeforeEllipsis () {
            return this.pagesInRange && this.pagesInRange[0] > 1;
        },
        showAfterEllipsis () {
            return !(this.current - this.rangeDelta + 1 > this.pageCount - this.pageRange);
        },

        previousRoute () {
            return {
                name: this.routeName,
                query: { offset: (this.previous - 1) * this.itemsPerPage },
            };
        },
        nextRoute () {
            return {
                name: this.routeName,
                query: { offset: (this.next - 1) * this.itemsPerPage },
            };
        },
    },

    methods: {
        goToPrevious () {
            if (this.previous) {
                this.$router.push(this.previousRoute);
            }
        },
        goToNext () {
            if (this.next) {
                this.$router.push(this.nextRoute);
            }
        },
    },
};
</script>
