<template>
    <div class="record_list-wrap-wrap">
        <div v-if="selectable" class="flex flex-wrap items-center gap-x-6 gap-y-2 mb-4">
            <div class="flex items-center">
                <!-- the same width as the select column, so the checkbox lines up with the rows' -->
                <span class="flex md:justify-center md:w-14 md:min-w-14 px-2">
                    <input :id="selectAllId"
                           v-model="allSelected"
                           type="checkbox"
                           :indeterminate.prop="someSelected">
                </span>
                <label :for="selectAllId" class="md:pl-2">Select all</label>
            </div>
            <span class="text-sm text-gray-600">{{ selected.length }} selected</span>
            <slot name="selectionActions" :selected-items="selectedItems" />
        </div>

        <table class="record_list-wrap hidden md:table">
            <thead class="record_list-headers">
                <tr>
                    <th v-if="selectable" class="w-14">
                        <span class="sr-only">Select</span>
                    </th>
                    <th v-for="(heading, i) in headings" :key="i" :class="cellClasses[i]">
                        <slot :name="`heading${i + 1}`" :heading-key="i">{{ heading }}</slot>
                    </th>
                </tr>
            </thead>
            <tbody>
                <slot name="tableRow" :items="items">
                    <tr v-for="(item, i) in items"
                        :key="i"
                        class="record_list-item"
                        :class="itemClasses(item)">
                        <td v-if="selectable"
                            class="record_list-col text-center cursor-pointer"
                            @mousedown="preventShiftSelect"
                            @click="selectClicked(item, $event)">
                            <!-- no id & label, as each row's rendered twice (a table & a list for mobile) -->
                            <input :checked="isSelected(item)"
                                   type="checkbox"
                                   :aria-label="selectLabel(item)">
                        </td>
                        <template v-for="(heading, j) in headings" :key="i + '-' + j">
                            <td :class="cellClassFunction(item, j)" class="record_list-col">
                                <slot :name="`col${j + 1}`" :item="item" :item-key="i" :cell-key="j" />
                            </td>
                        </template>
                    </tr>
                </slot>
            </tbody>
        </table>

        <ul class="record_list-wrap md:hidden">
            <slot name="listRow" :items="items">
                <li v-for="(item, i) in items"
                    :key="i"
                    class="record_list-item"
                    :class="itemClasses(item)">
                    <div v-if="selectable"
                         class="record_list-col flex items-center gap-x-2 cursor-pointer"
                         @mousedown="preventShiftSelect"
                         @click="selectClicked(item, $event)">
                        <input :checked="isSelected(item)"
                               type="checkbox"
                               :aria-label="selectLabel(item)">
                        <span aria-hidden="true">Select</span>
                    </div>
                    <template v-for="(heading, j) in headings" :key="i + '-' + j">
                        <div :class="cellClassFunction(item, j)" class="record_list-col">
                            <div v-if="heading" class="record_list-mobile_heading">{{ heading }}</div>
                            <slot :name="`col${j + 1}`" :item="item" :item-key="i" :cell-key="j" />
                        </div>
                    </template>
                </li>
            </slot>
        </ul>
    </div>
</template>

<script setup>
import { computed, watch, watchEffect } from 'vue';
import { createId } from '@paralleldrive/cuid2';

/**
 * The keys (itemKey) of the ticked items, when selectable.
 * Only listed items stay ticked, & none do when it's not selectable.
 */
const selected = defineModel('selected', { type: Array, default: () => [] });

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
    /**
     * Adds a column of checkboxes before the others & a toolbar above the list with Select all,
     * the count & the selectionActions slot (given selectedItems). Bind the keys with v-model:selected.
     */
    selectable: {
        type: Boolean,
        default: false,
    },
    /**
     * The item's property that identifies it in selected, eg expenseId. Required when selectable.
     */
    itemKey: {
        type: String,
        default: null,
    },
    /**
     * Returns the checkbox's aria-label for the item, eg "Select invoice cts-i001".
     */
    selectLabel: {
        type: Function,
        default () {
            return 'Select';
        },
    },
});

const cellClassFunction = (item, j) => [ props.cellClasses[j] ?? null, props.cellClassesFunction(item, j) ];

// without it every row's key would be undefined, so ticking one would tick them all
watchEffect(() => {
    if (props.selectable && !props.itemKey) {
        throw new Error('RecordList: itemKey is required when it\'s selectable.');
    }
});

const keyOf = (item) => item[props.itemKey];
const isSelected = (item) => props.selectable && selected.value.includes(keyOf(item));
const itemClasses = (item) => [ props.rowClasses(item), { 'record_list-item-selected': isSelected(item) } ];

// the row last ticked or unticked, where a shift-click's range starts
let lastClickedKey = null;

// with shift, the rows from the last one clicked to this one, if it's still listed
const clickedKeys = (item, event) => {
    const lastIndex = props.items.findIndex((listed) => keyOf(listed) === lastClickedKey);
    if (!event.shiftKey || -1 === lastIndex) {
        return [ keyOf(item) ];
    }

    const index = props.items.indexOf(item);

    return props.items
        .slice(Math.min(lastIndex, index), Math.max(lastIndex, index) + 1)
        .map(keyOf);
};

// a click on the checkbox or anywhere in its cell (the checkbox's bubbles up, as its change event has no shiftKey);
// the rows in a shift-click's range all take the clicked row's new state
const selectClicked = (item, event) => {
    const keys = clickedKeys(item, event);
    if (isSelected(item)) {
        selected.value = selected.value.filter((key) => !keys.includes(key));
    } else {
        selected.value = [ ...selected.value, ...keys.filter((key) => !selected.value.includes(key)) ];
    }

    lastClickedKey = keyOf(item);
};

// stops a shift-click selecting the text between the clicks
const preventShiftSelect = (event) => {
    if (event.shiftKey) {
        event.preventDefault();
    }
};

const selectAllId = createId();
const selectedItems = computed(() => props.items.filter((item) => isSelected(item)));
const allSelected = computed({
    get: () => props.items.length > 0 && selectedItems.value.length === props.items.length,
    set: (checked) => {
        selected.value = checked ? props.items.map(keyOf) : [];
    },
});
const someSelected = computed(() => selectedItems.value.length > 0 && !allSelected.value);

// only what's listed can stay ticked
watch(() => [ props.items, props.selectable ], () => {
    const listed = selected.value.filter((key) => props.selectable && props.items.some((item) => keyOf(item) === key));
    if (listed.length !== selected.value.length) {
        selected.value = listed;
    }
});
</script>
