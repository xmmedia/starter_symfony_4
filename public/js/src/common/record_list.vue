<template>
    <div class="record_list-wrap-wrap">
        <table class="record_list-wrap hidden md:table">
            <thead class="record_list-headers">
                <tr>
                    <th v-for="(heading,i) in headings" :key="i" :class="cellClasses[i]">{{ heading }}</th>
                </tr>
            </thead>
            <tbody>
                <slot name="tableRow" :items="items">
                    <tr v-for="(item,i) in items" :key="i" class="record_list-item" :class="rowClasses(item)">
                        <template v-for="(heading,j) in headings" :key="i+'-'+j">
                            <td :class="cellClasses[j]" class="record_list-col">
                                <slot :name="`col${j+1}`" :item="item"></slot>
                            </td>
                        </template>
                    </tr>
                </slot>
            </tbody>
        </table>

        <ul class="record_list-wrap md:hidden">
            <slot name="listRow" :items="items">
                <li v-for="(item,i) in items" :key="i" class="record_list-item" :class="rowClasses(item)">
                    <template v-for="(heading,j) in headings" :key="i+'-'+j">
                        <div :class="cellClasses[j]" class="record_list-col">
                            <div v-if="heading" class="record_list-mobile_heading">{{ heading }}</div>
                            <slot :name="`col${j+1}`" :item="item"></slot>
                        </div>
                    </template>
                </li>
            </slot>
        </ul>
    </div>
</template>

<script setup>
defineProps({
    headings: {
        type: Array,
        default: function () {
            return [];
        },
    },
    items: {
        type: Array,
        default: function () {
            return [];
        },
    },
    cellClasses: {
        type: Array,
        default: function () {
            return [];
        },
    },
    rowClasses: {
        type: Function,
        default () {
            return null;
        },
    },
});
</script>
