<template>
    <div class="record_list-wrap-wrap">
        <table class="record_list-wrap hidden md:table">
            <thead class="record_list-headers">
                <tr>
                    <th v-for="(heading,i) in headings" :key="i" :class="cellClasses[i]">
                        <slot :name="`heading${i+1}`" :heading-key="i">{{ heading }}</slot>
                    </th>
                </tr>
            </thead>
            <tbody>
                <slot name="tableRow" :items="items">
                    <tr v-for="(item,i) in items" :key="i" class="record_list-item" :class="rowClasses(item)">
                        <template v-for="(heading,j) in headings" :key="i+'-'+j">
                            <td :class="cellClassFunction(item, j)" class="record_list-col">
                                <slot :name="`col${j+1}`" :item="item" :item-key="i" :cell-key="j"></slot>
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
                        <div :class="cellClassFunction(item, j)" class="record_list-col">
                            <div v-if="heading" class="record_list-mobile_heading">{{ heading }}</div>
                            <slot :name="`col${j+1}`" :item="item" :item-key="i" :cell-key="j"></slot>
                        </div>
                    </template>
                </li>
            </slot>
        </ul>
    </div>
</template>

<script setup>
const props = defineProps({
    headings: {
        type: Array,
        default: () => [],
    },
    items: {
        type: Array,
        default: () => [],
    },
    cellClasses: {
        type: Array,
        default: () => [],
    },
    /**
     * Returns string of classes for the cell.
     * Arguments:
     * - item: The current item for the row
     * - j: The index of the cell
     */
    cellClassesFunction: {
        type: Function,
        default () {
            return null;
        },
    },
    rowClasses: {
        type: Function,
        default () {
            return null;
        },
    },
});

const cellClassFunction = (item, j) => [props.cellClasses[j] ?? null, props.cellClassesFunction(item, j)];
</script>
