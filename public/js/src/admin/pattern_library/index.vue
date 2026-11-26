<template>
    <Teleport to="#header-page-title"><span>Pattern Library</span></Teleport>
    <Teleport to="#header-actions" />

    <div class="max-w-5xl">
        <!-- ############################# -->
        <!-- ####### Headings ######### -->
        <!-- ############################# -->
        <h2>Headings</h2>
        <h1>Heading 1<br>Heading 1</h1>
        <h2>Heading 2<br>Heading 2</h2>
        <h3>Heading 3<br>Heading 3</h3>
        <h4>Heading 4<br>Heading 4</h4>
        <h5>Heading 5<br>Heading 5</h5>
        <h6>Heading 6<br>Heading 6</h6>

        <!-- ############################# -->
        <!-- ########## Text ############# -->
        <!-- ############################# -->
        <h2>Text</h2>
        <p>Paragraph text – 16px – Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut aliquet tincidunt urna,
            ac molestie elit viverra sed. Maecenas rhoncus nisl purus, eget finibus mauris venenatis sit amet. Nunc
            tincidunt sed magna a tincidunt. Maecenas nunc ex, imperdiet in scelerisque in, sodales vel arcu.
            Pellentesque fringilla mattis est eget egestas. Mauris eu lorem quis nisl sagittis euismod.
            Sed ultricies, augue in condimentum venenatis, turpis lectus sagittis elit, vitae porttitor eros eros quis
            ipsum. Quisque sagittis enim quis nulla pretium, vel accumsan magna ullamcorper.
            Donec vitae sollicitudin libero.
        </p>
        <p>Donec facilisis egestas nulla vel condimentum. Praesent quis lectus nec velit rhoncus ultricies ac quis mi.
            Mauris mattis lacus magna, venenatis faucibus nisi rhoncus non. Aenean ut facilisis nisl. Nam ullamcorper
            nunc augue, at dignissim neque cursus nec. Nunc ut lorem at tortor malesuada tempus ac sed felis. Mauris
            egestas leo eget justo vehicula iaculis. Cras rutrum malesuada pulvinar. Fusce porta augue quis feugiat
            consectetur. Nam pharetra erat eu efficitur placerat. Phasellus non placerat sapien. Interdum et malesuada
            fames ac ante ipsum primis in faucibus.
        </p>

        <div class="max-w-lg mx-auto">
            <p>Narrow content… Donec facilisis egestas nulla vel condimentum. Praesent quis lectus nec velit rhoncus
                ultricies ac quis mi. Mauris mattis lacus magna, venenatis faucibus nisi rhoncus non. Aenean ut
                facilisis nisl. Nam ullamcorper nunc augue, at dignissim neque cursus nec. Nunc ut lorem at tortor
                malesuada tempus ac sed felis. Mauris egestas leo eget justo vehicula iaculis. Cras rutrum malesuada
                pulvinar. Fusce porta augue quis feugiat consectetur. Nam pharetra erat eu efficitur placerat.
                Phasellus non placerat sapien. Interdum et malesuada fames ac ante ipsum primis in faucibus.
            </p>
        </div>

        <strong>Bold Text</strong><br>
        <em>Italic Text</em><br>

        <a href="">Link</a><br>
        <a href=""><strong>Bold Link</strong></a><br>

        <!-- ############################# -->
        <!-- ##### Unordered List ######## -->
        <!-- ############################# -->
        <h2>Unordered List</h2>
        <ul>
            <li>List item 1</li>
            <li>List item 2</li>
            <li>List item 3</li>
            <li>List item 4</li>
        </ul>

        <!-- ############################# -->
        <!-- ###### Ordered List ######### -->
        <!-- ############################# -->
        <h2>Ordered List</h2>
        <ol>
            <li>List item 1</li>
            <li>List item 2</li>
            <li>List item 3</li>
            <li>List item 4</li>
        </ol>
    </div>

    <!-- ############################# -->
    <!-- ######### Buttons ########### -->
    <!-- ############################# -->
    <!-- outside max-w-5xl so the state columns have room -->
    <!-- data-pl-state shows the state without interacting (see common/pattern_library_states.js) -->
    <div v-for="section in buttonSections"
         :key="section.heading"
         :class="section.class">
        <h2>{{ section.heading }}</h2>
        <div class="overflow-x-auto">
            <table class="w-full mt-4 border-collapse">
                <thead>
                    <tr class="border-b-2 border-b-gray-200">
                        <th class="p-2 text-left">Description</th>
                        <th class="p-2 text-left">Classes</th>
                        <th class="p-2 text-left">Element</th>
                        <th v-for="state in buttonStates" :key="state.value" class="p-2 text-left">
                            {{ state.label }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in buttonRows" :key="row.label + row.tag" class="border-b border-b-gray-200">
                        <td class="p-2">{{ row.label }}</td>
                        <td class="p-2"><code v-if="row.class">{{ row.class }}</code></td>
                        <td class="p-2"><code>&lt;{{ row.tag }}&gt;</code></td>
                        <td v-for="state in buttonStates" :key="state.value" class="p-2 whitespace-nowrap">
                            <!-- a disabled button can't be focused or pressed -->
                            <template v-if="!row.disabled || [ '', 'hover' ].includes(state.value)">
                                <a v-if="'a' === row.tag"
                                   href=""
                                   :class="row.class"
                                   :tabindex="state.value ? -1 : undefined"
                                   :data-pl-state="state.value || undefined">{{ row.label }}</a>
                                <button v-else
                                        :class="row.class"
                                        :disabled="row.disabled"
                                        :tabindex="state.value ? -1 : undefined"
                                        :data-pl-state="state.value || undefined">{{ row.label }}</button>
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="max-w-5xl">
        <!-- ############################# -->
        <!-- ########## Forms ############ -->
        <!-- ############################# -->
        <!--
        IMPORTANT
        Spaces between inputs and labels that are inline with each other (beside)
        cause issues with formatting. Leave the HTML formatting as is!
        -->
        <div class="form-wrap">
            <h2 class="-mt-2">Forms</h2>
            <p>Some instructions…</p>
            <form>

                <div class="field-wrap">
                    <label for="pattern-library-text">Text field</label>
                    <ul class="field-errors">
                        <li>Error</li>
                    </ul>
                    <input id="pattern-library-text" type="text" placeholder="Placeholder">
                    <div class="field-help">Use at least 8 characters.</div>
                </div>

                <FieldInput v-model="textValue">Text field component</FieldInput>
                <FieldEmail v-model="emailValue">Email field component</FieldEmail>
                <FieldTextarea v-model="textareaValue">Textarea field component</FieldTextarea>

                <div class="field-wrap field-wrap-readonly">
                    <label for="pattern-library-text-readonly">Read-only field</label>
                    <input id="pattern-library-text-readonly" type="text" readonly>
                </div>

                <div class="sm:flex">
                    <div class="field-wrap w-1/2 mr-2">
                        <label for="pattern-library-full_width-1">First field</label>
                        <input id="pattern-library-full_width-1" type="text">
                    </div>
                    <div class="field-wrap w-1/2">
                        <label for="pattern-library-full_width-2">Second field</label>
                        <input id="pattern-library-full_width-2" type="text">
                    </div>
                </div>

                <div class="field-wrap">
                    <label for="pattern-library-prefix-suffix">Field with prefix beside</label>
                    <span class="mr-2">Prefix Beside</span>
                    <input id="pattern-library-prefix-suffix" type="text" class="w-auto">
                    <span class="ml-2">Suffix</span>
                </div>
                <div class="field-wrap">
                    <label for="pattern-library-textarea">Text area</label>
                    <textarea id="pattern-library-textarea" class="h-32" />
                </div>
                <div class="field-wrap">
                    <label for="pattern-library-file">File input</label>
                    <input id="pattern-library-file" type="file">
                    <div class="field-help">Accepted formats: PDF, JPG, PNG.</div>
                </div>
                <FieldFile v-model="fileValue" accept=".pdf,application/pdf">
                    File field component
                    <template #help>Accepted format: PDF.</template>
                </FieldFile>
                <FieldFile v-model="imageValue" accept="image/*" :preview="true">
                    Image field component with preview
                </FieldFile>
                <FieldFile v-model="existingFileValue">
                    File field component with an existing file
                </FieldFile>
                <FieldFile v-model="notRemovableFileValue" :removable="false">
                    File field component with an existing file that can't be removed
                </FieldFile>
                <div class="field-wrap">
                    <label for="pattern-library-select">Select</label>
                    <select id="pattern-library-select">
                        <option value="">Test 1</option>
                        <option value="">Test 2</option>
                    </select>
                </div>
                <div class="field-wrap">
                    <label for="pattern-library-select-focus">Focused select</label>
                    <select id="pattern-library-select-focus" tabindex="-1" data-pl-state="focus focus-visible">
                        <option value="">Test 1</option>
                    </select>
                </div>

                <div class="field-wrap">
                    <label for="pattern-library-multiselect-single">Multiselect (single)</label>
                    <VueMultiselect id="pattern-library-multiselect-single"
                                    v-model="multiselectSingleValue"
                                    :options="multiselectOptions"
                                    placeholder="Select one" />
                </div>
                <div class="field-wrap">
                    <label for="pattern-library-multiselect-multiple">Multiselect (multiple)</label>
                    <VueMultiselect id="pattern-library-multiselect-multiple"
                                    v-model="multiselectMultipleValue"
                                    :options="multiselectOptions"
                                    :multiple="true"
                                    :close-on-select="false"
                                    placeholder="Select some" />
                </div>
                <div class="field-wrap">
                    <label for="pattern-library-multiselect-disabled">Multiselect (disabled)</label>
                    <VueMultiselect id="pattern-library-multiselect-disabled"
                                    v-model="multiselectDisabledValue"
                                    :options="multiselectOptions"
                                    :disabled="true" />
                </div>

                <div class="field-wrap">
                    <label for="pattern-library-date">Date picker</label>
                    <FlatPickr id="pattern-library-date"
                               v-model="dateValue"
                               :config="dateConfig"
                               placeholder="Select a date…"
                               class="w-48" />
                </div>
                <div class="field-wrap">
                    <label for="pattern-library-date-time">Date & time picker</label>
                    <FlatPickr id="pattern-library-date-time"
                               v-model="dateTimeValue"
                               :config="dateTimeConfig"
                               placeholder="Select a date & time…"
                               class="w-56" />
                </div>
                <div class="field-wrap">
                    <label for="pattern-library-date-range">Date range picker</label>
                    <FlatPickr id="pattern-library-date-range"
                               v-model="dateRangeValue"
                               :config="dateRangeConfig"
                               placeholder="Select a range…"
                               class="w-64" />
                </div>

                <div class="field-wrap">
                    <div class="field-wrap-checkbox">
                        <input id="pattern-library-checkbox-1"
                               type="checkbox"
                               name="pattern-library-checkbox-1">
                        <label for="pattern-library-checkbox-1">Checkbox label</label>
                    </div>
                    <div class="field-help">Some help…</div>
                </div>
                <FieldCheckbox v-model="checkboxValue">
                    Checkbox label
                    <template #help>Some help…</template>
                </FieldCheckbox>
                <div class="field-wrap">
                    <div class="field-wrap-checkbox">
                        <input id="pattern-library-checkbox-2"
                               type="checkbox"
                               name="pattern-library-checkbox-2">
                        <label for="pattern-library-checkbox-2">
                            Checkbox label Maecenas sed diam eget risus varius blandit sit amet non magna.
                            Duis mollis, est non commodo luctus, nisi erat porttitor ligula,
                            eget lacinia odio sem nec elit.
                        </label>
                    </div>
                    <div class="field-help">Some help…</div>
                </div>
                <div class="field-wrap field-wrap-checkbox">
                    <input id="pattern-library-checkbox-3"
                           type="checkbox"
                           name="pattern-library-checkbox-3">
                    <label for="pattern-library-checkbox-3">Checkbox label</label>
                </div>
                <div class="field-wrap field-wrap-checkbox">
                    <input id="pattern-library-checkbox-long-4"
                           type="checkbox"
                           name="pattern-library-checkbox-long-4">
                    <label for="pattern-library-checkbox-long-4">
                        Checkbox label Maecenas sed diam eget risus varius blandit sit amet non magna.
                        Duis mollis, est non commodo luctus, nisi erat porttitor ligula,
                        eget lacinia odio sem nec elit.
                    </label>
                </div>

                <FieldRadios :values="radios1Values">
                    Horizontal radios
                    <template #help>Some help…</template>
                </FieldRadios>
                <fieldset class="field-wrap-radios">
                    <legend>Horizontal radios</legend>
                    <div class="flex">
                        <div class="field-wrap-radio">
                            <input id="pattern-library-radio-01"
                                   type="radio"
                                   name="pattern-library-radio-1">
                            <label for="pattern-library-radio-01">Option 1</label>
                        </div>
                        <div class="field-wrap-radio">
                            <input id="pattern-library-radio-02"
                                   type="radio"
                                   name="pattern-library-radio-1">
                            <label for="pattern-library-radio-02">Option 2</label>
                        </div>
                        <div class="field-wrap-radio">
                            <input id="pattern-library-radio-03"
                                   type="radio"
                                   name="pattern-library-radio-1">
                            <label for="pattern-library-radio-03">Option 3</label>
                        </div>
                    </div>
                    <div class="field-help">Some help…</div>
                </fieldset>

                <FieldRadios :values="radios2Values" :row="false">
                    Vertical radios
                    <template #help>Some help…</template>
                </FieldRadios>
                <fieldset class="field-wrap-radios">
                    <legend>Vertical Radios</legend>
                    <div class="field-wrap-radio">
                        <input id="pattern-library-radio-04"
                               type="radio"
                               name="pattern-library-radio-2">
                        <label for="pattern-library-radio-04">Radio button</label>
                    </div>
                    <div class="field-wrap-radio">
                        <input id="pattern-library-radio-05"
                               type="radio"
                               name="pattern-library-radio-2">
                        <label for="pattern-library-radio-05">Radio button</label>
                    </div>
                    <div class="field-wrap-radio">
                        <input id="pattern-library-radio-06"
                               type="radio"
                               name="pattern-library-radio-2">
                        <label for="pattern-library-radio-06">Radio button</label>
                    </div>
                    <div class="field-wrap-radio">
                        <input id="pattern-library-radio-07"
                               type="radio"
                               name="pattern-library-radio-2">
                        <label for="pattern-library-radio-07">
                            Radio button Maecenas sed diam eget risus varius blandit sit amet non magna.
                            Duis mollis, est non commodo luctus, nisi erat porttitor ligula,
                            eget lacinia odio sem nec elit.
                        </label>
                    </div>
                    <div class="field-help">Some help…</div>
                </fieldset>

                <FieldCheckboxes v-model="checkboxesValue" :values="checkboxesValues">
                    Multiple checkboxes
                </FieldCheckboxes>
                <fieldset class="field-wrap-checkboxes">
                    <legend>Multiple checkboxes</legend>
                    <div class="field-wrap-checkbox">
                        <input id="pattern-library-checkbox-01"
                               type="checkbox"
                               name="pattern-library-checkbox1[]">
                        <label for="pattern-library-checkbox-01">Checkbox</label>
                    </div>
                    <div class="field-wrap-checkbox">
                        <input id="pattern-library-checkbox-02"
                               type="checkbox"
                               name="pattern-library-checkbox1[]">
                        <label for="pattern-library-checkbox-02">Checkbox</label>
                    </div>
                    <div class="field-wrap-checkbox">
                        <input id="pattern-library-checkbox-03"
                               type="checkbox"
                               name="pattern-library-checkbox1[]">
                        <label for="pattern-library-checkbox-03">Checkbox</label>
                    </div>
                    <div class="field-wrap-checkbox">
                        <input id="pattern-library-checkbox-03a"
                               type="checkbox"
                               name="pattern-library-checkbox1[]">
                        <label for="pattern-library-checkbox-03a">
                            Checkbox Maecenas sed diam eget risus varius blandit sit amet non magna.
                            Duis mollis, est non commodo luctus, nisi erat porttitor ligula,
                            eget lacinia odio sem nec elit.
                        </label>
                    </div>
                    <div class="field-help">Some help…</div>
                </fieldset>

                <div class="field-wrap">
                    <label for="pattern-library-two-fields-1">2 Fields beside each other</label>
                    <input id="pattern-library-two-fields-1" type="text" class="w-auto mr-2">
                    <select id="pattern-library-two-fields-2" class="w-auto">
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">…</option>
                    </select>
                </div>

                <div class="field-wrap">
                    <label for="pattern-library-separator-1">Fields with separator</label>
                    <div class="flex items-center">
                        <select id="pattern-library-separator-1" class="mr-1">
                            <option>––</option>
                        </select>
                        <div class="mr-1">–</div>
                        <select id="pattern-library-separator-2" class="mr-1">
                            <option>––</option>
                        </select>
                        <div class="mr-1">–</div>
                        <select id="pattern-library-separator-3">
                            <option>––</option>
                        </select>
                    </div>
                </div>

                <FieldRadios :values="radiosPillsValues" :row="false" :pills="true">
                    Radio pills component
                    <template #help>Some help…</template>
                </FieldRadios>
                <fieldset class="field-wrap-radios mb-5">
                    <legend>Radio pills</legend>
                    <div class="flex flex-wrap gap-2">
                        <span class="radio-pill radio-pill-green">
                            <input id="radio-pill-1"
                                   name="radio-pill"
                                   value="yes"
                                   type="radio">
                            <label for="radio-pill-1">Yes</label>
                        </span>

                        <span class="radio-pill radio-pill-red">
                            <input id="radio-pill-2"
                                   name="radio-pill"
                                   value="no"
                                   type="radio">
                            <label for="radio-pill-2">No</label>
                        </span>
                    </div>
                </fieldset>
                <fieldset class="field-wrap-radios mb-5">
                    <legend>Radio pill states</legend>
                    <div class="flex flex-wrap gap-2">
                        <span class="radio-pill radio-pill-green">
                            <input id="radio-pill-state-1"
                                   name="radio-pill-state"
                                   type="radio"
                                   tabindex="-1"
                                   data-pl-state="hover">
                            <label for="radio-pill-state-1">Hover</label>
                        </span>

                        <span class="radio-pill radio-pill-green">
                            <input id="radio-pill-state-2"
                                   name="radio-pill-state"
                                   type="radio"
                                   tabindex="-1"
                                   data-pl-state="focus focus-visible">
                            <label for="radio-pill-state-2">Focus</label>
                        </span>

                        <span class="radio-pill radio-pill-green">
                            <input id="radio-pill-state-3"
                                   name="radio-pill-state"
                                   type="radio"
                                   checked
                                   tabindex="-1"
                                   data-pl-state="focus focus-visible">
                            <label for="radio-pill-state-3">Checked &amp; Focus</label>
                        </span>
                    </div>
                </fieldset>

                <FieldCheckbox v-model="checkboxPillValue" :pill="true">
                    Checkbox pill component
                </FieldCheckbox>

                <fieldset class="mt-6 mb-4 p-4 border border-gray-300">
                    <legend>Fieldset with border</legend>
                    <div class="field-wrap">
                        <label for="pattern-library-fieldset-1">Fieldset field 1</label>
                        <select id="pattern-library-fieldset-1">
                            <option>––</option>
                        </select>
                    </div>

                    <div class="text-right">
                        <a href="" class="form-action">× Remove</a>
                    </div>
                </fieldset>

                <!-- fixed suggestions, so it shows without a Google key -->
                <div class="field-wrap relative mb-44">
                    <label for="pattern-library-place-suggestions">Address suggestions</label>
                    <input id="pattern-library-place-suggestions"
                           type="text"
                           value="10 Main"
                           autocomplete="off">
                    <PlaceSuggestions list-id="pattern-library-place-suggestions-list"
                                      :suggestions="placeSuggestions"
                                      :active-index="placeSuggestionsActiveIndex"
                                      @activate="placeSuggestionsActiveIndex = $event" />
                </div>

                <div class="mt-6 mb-4 p-4 pt-3 border border-gray-300">
                    <div class="flex">
                        <h4 class="mt-0 mb-3">Heading</h4>
                        <div class="ml-auto leading-none">
                            <a href="" class="form-action">Action</a>
                        </div>
                    </div>

                    <fieldset class="field-wrap-checkboxes mb-0">
                        <legend>Multiple checkboxes</legend>
                        <div class="field-wrap-checkbox">
                            <input id="pattern-library-checkbox-04"
                                   type="checkbox"
                                   name="pattern-library-checkbox2[]">
                            <label for="pattern-library-checkbox-04">Checkbox</label>
                        </div>
                        <div class="field-wrap-checkbox">
                            <input id="pattern-library-checkbox-05"
                                   type="checkbox"
                                   name="pattern-library-checkbox2[]">
                            <label for="pattern-library-checkbox-05">Checkbox</label>
                        </div>
                        <div class="field-wrap-checkbox">
                            <input id="pattern-library-checkbox-06"
                                   type="checkbox"
                                   name="pattern-library-checkbox2[]">
                            <label for="pattern-library-checkbox-06">Checkbox</label>
                        </div>
                        <div class="field-help">Some help…</div>
                    </fieldset>
                </div>

                <button class="button">Primary Button</button>
                <button class="form-action button-link">Cancel Link</button>
                <a href="" class="form-action">Go Back</a>
                <div class="field-help">Form button help.</div>

                <ul class="form-extra_actions">
                    <li>
                        <AdminDelete record-desc="Fake" />
                    </li>
                    <li>
                        <a href="" class="form-action">Other Action</a>
                    </li>
                </ul>

            </form>
        </div>

        <!-- ############################# -->
        <!-- ########## Header ########### -->
        <!-- ############################# -->
        <h2>Header</h2>
        <div class="header-wrap">
            <div class="header-page_title">Page Name</div>
            <div class="header-actions">
                <a href="" class="header-action header-action-main">Action Main</a>
                <a href="" class="header-action">Secondary Action</a>
                <ActionMenu class="action_menu-header">
                    <a href="">Other Action</a>
                    <button type="button">Another Action</button>
                    <button type="button" data-keep-open>Keeps the Menu Open</button>
                </ActionMenu>
                <div class="button_pair-wrap header-button_pair">
                    <a href="" class="button_pair-button">&lt;</a>
                    <a href="" class="button_pair-button">&gt;</a>
                </div>
                <div class="header-secondary_actions">
                    <a href="">Less Important</a>
                    <a href="">Less Important</a>
                </div>
            </div>
        </div>

        <!-- ############################# -->
        <!-- ####### Button Pair ######### -->
        <!-- ############################# -->
        <h2>Button Pair</h2>
        <div class="header-wrap">
            <div class="header-page_title">Both Inactive</div>
            <div class="header-actions">
                <a href="" class="header-action header-action-main">Action Main</a>
                <div class="button_pair-wrap header-button_pair">
                    <div class="button_pair-button button_pair-inactive">&lt;</div>
                    <div class="button_pair-button button_pair-inactive">&gt;</div>
                </div>
            </div>
        </div>

        <div class="header-wrap">
            <div class="header-page_title">Both Active</div>
            <div class="header-actions">
                <div class="button_pair-wrap header-button_pair">
                    <a href="" class="button_pair-button">&lt;</a>
                    <a href="" class="button_pair-button">&gt;</a>
                </div>
            </div>
        </div>

        <div class="header-wrap">
            <div class="header-page_title">Right Active</div>
            <div class="header-actions">
                <div class="button_pair-wrap header-button_pair">
                    <div class="button_pair-button button_pair-inactive">&lt;</div>
                    <a href="" class="button_pair-button">&gt;</a>
                </div>
            </div>
        </div>

        <div class="header-wrap">
            <div class="header-page_title">Left Active</div>
            <div class="header-actions">
                <div class="button_pair-wrap header-button_pair">
                    <a href="" class="button_pair-button">&lt;</a>
                    <div class="button_pair-button button_pair-inactive">&gt;</div>
                </div>
            </div>
        </div>

        <!-- ############################# -->
        <!-- ####### Record List ######### -->
        <!-- ############################# -->
        <div class="my-12">
            <h2>Record List</h2>
            <h4 class="mt-8 mb-2">RecordList component</h4>

            <div class="record_list-record_count">Showing X of X</div>
            <!-- md:text-center is needed for the headings as the flexbox styling isn't applied to headings -->
            <RecordList :headings="[ 'Col 1', 'Col 1', 'Col Action', 'Long Value', 'Active', 'Inactive', '' ]"
                        :items="recordListItems"
                        :cell-classes="[
                            null, null, null, null, 'md:text-center', 'md:text-center', 'record_list-col-actions'
                        ]">
                <template #col1="{ item }">
                    {{ item.col1 }}
                </template>
                <template #col2="{ item }">
                    {{ item.col2 }}
                </template>
                <template #col3="{ item }">
                    {{ item.col3 }}
                </template>
                <template #col4="{ item }">
                    {{ item.col4 }}
                </template>
                <template #col5="{ item }">
                    <AdminIconCheck :active="item.col5" />
                </template>
                <template #col6="{ item }">
                    <AdminIconCheck :active="item.col6" />
                </template>
                <template #col7>
                    <a href="">Edit</a>
                    <button type="button" class="button-link">Send reminder</button>
                </template>
            </RecordList>

            <h4 class="mt-8 mb-2">RecordList with several actions</h4>
            <RecordList :headings="[ 'Name', 'Expiry Date', 'Outstanding', 'Client', 'Price', 'Active', '' ]"
                        :items="recordListActionItems"
                        :cell-classes="[
                            null, null, 'md:text-center', null, null, 'md:text-center', 'record_list-col-actions'
                        ]">
                <template #col1="{ item }">
                    <a href="">{{ item.name }}</a>
                </template>
                <template #col2="{ item }">
                    {{ item.expiryDate }}
                    <div v-if="!item.renewed" class="text-sm italic text-gray-500">Not renewed yet</div>
                </template>
                <template #col3="{ item }">
                    <AdminIconCheck :active="item.outstanding" />
                </template>
                <template #col4="{ item }">
                    <a href="">{{ item.client }}</a>
                </template>
                <template #col5="{ item }">
                    {{ item.price }}
                </template>
                <template #col6="{ item }">
                    <AdminIconCheck :active="item.active" />
                </template>
                <template #col7="{ item }">
                    <a href="">Edit</a>
                    <ActionMenu>
                        <template v-if="item.renewed">
                            <a href="">Add Renewal</a>
                            <button type="button">Invoice in Advance</button>
                        </template>
                        <button v-else type="button">Use Renewal</button>
                    </ActionMenu>
                </template>
            </RecordList>

            <h4 class="mt-8 mb-2">RecordList with selectable rows</h4>
            <RecordList v-model:selected="selectedRecordIds"
                        :headings="[ 'Name', 'Client', 'Price' ]"
                        :items="recordListActionItems"
                        selectable
                        item-key="id"
                        :select-label="(item) => `Select ${item.name}`">
                <template #selectionActions="{ selectedItems }">
                    <button type="button" class="button-link" :disabled="0 === selectedItems.length">Renew</button>
                    <button type="button" class="button-link" :disabled="0 === selectedItems.length">
                        Deactivate
                    </button>
                </template>
                <template #col1="{ item }">
                    <a href="">{{ item.name }}</a>
                </template>
                <template #col2="{ item }">
                    {{ item.client }}
                </template>
                <template #col3="{ item }">
                    {{ item.price }}
                </template>
            </RecordList>

            <h4 class="mt-8 mb-2">Custom table (hidden below md)</h4>
            <table class="record_list-wrap hidden md:table">
                <thead class="record_list-headers">
                    <tr>
                        <th>Col 1</th>
                        <th>Col 2</th>
                        <th>Col Action</th>
                        <th>Long Value</th>
                        <th class="text-center">Active</th>
                        <th class="text-center">Inactive</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in 3" :key="i" class="record_list-item">
                        <td class="record_list-col">
                            <a href="">Value 1-{{ i }}</a>
                        </td>
                        <td class="record_list-col">
                            Value 2-{{ i }}
                        </td>
                        <td class="record_list-col">
                            <a href="" class="block">Action 1</a>
                            <a href="" class="block">Action 2</a>
                        </td>
                        <td>
                            Nullam quis risus eget urna mollis ornare vel eu leo.
                            Nullam quis risus eget urna mollis ornare vel eu leo.
                        </td>
                        <td class="record_list-col">
                            <AdminIconCheck :active="true" />
                            <span class="record_list-mobile_heading">[True]</span>
                        </td>
                        <td class="record_list-col">
                            <AdminIconCheck :active="false" />
                            <span class="record_list-mobile_heading">[False]</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h4 class="mt-8 mb-2">Custom mobile (hidden above md)</h4>
            <ul class="record_list-wrap md:hidden">
                <li v-for="i in 3" :key="i" class="record_list-item">
                    <div class="record_list-col">
                        <div class="record_list-mobile_heading">Value 1</div>
                        <a href="">Value 1-{{ i }}</a>
                    </div>
                    <div class="record_list-col">
                        <div class="record_list-mobile_heading">Value 2</div>
                        <a href="">Value 2-{{ i }}</a>
                    </div>
                    <div class="record_list-col">
                        <a href="" class="block">Action 1</a>
                        <a href="" class="block">Action 2</a>
                    </div>
                    <div class="record_list-col">
                        <AdminIconCheck :active="true" />
                        <span class="record_list-mobile_heading">[True]</span>
                    </div>
                    <div class="record_list-col">
                        <AdminIconCheck :active="false" />
                        <span class="record_list-mobile_heading">[False]</span>
                    </div>
                    <div class="record_list-col">
                        Nullam quis risus eget urna mollis ornare vel eu leo.
                        Nullam quis risus eget urna mollis ornare vel eu leo.
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!-- outside max-w-5xl to show a list at full width -->
    <div class="my-12">
        <h4 class="mb-2">RecordList at full width</h4>
        <RecordList :headings="[ 'Name', 'Expiry Date', 'Outstanding', 'Client', 'Price', 'Active', '' ]"
                    :items="recordListActionItems"
                    :cell-classes="[
                        null, null, 'md:text-center', null, null, 'md:text-center', 'record_list-col-actions'
                    ]">
            <template #col1="{ item }">
                <a href="">{{ item.name }}</a>
            </template>
            <template #col2="{ item }">
                {{ item.expiryDate }}
                <div v-if="!item.renewed" class="text-sm italic text-gray-500">Not renewed yet</div>
            </template>
            <template #col3="{ item }">
                <AdminIconCheck :active="item.outstanding" />
            </template>
            <template #col4="{ item }">
                <a href="">{{ item.client }}</a>
            </template>
            <template #col5="{ item }">
                {{ item.price }}
            </template>
            <template #col6="{ item }">
                <AdminIconCheck :active="item.active" />
            </template>
            <template #col7="{ item }">
                <a href="">Edit</a>
                <ActionMenu>
                    <template v-if="item.renewed">
                        <a href="">Add Renewal</a>
                        <button type="button">Invoice in Advance</button>
                    </template>
                    <button v-else type="button">Use Renewal</button>
                </ActionMenu>
            </template>
        </RecordList>
    </div>

    <div class="max-w-5xl">
        <!-- ############################# -->
        <!-- ####### Record View ######### -->
        <!-- ############################# -->
        <h2>Record View</h2>
        <div class="record_view-item_wrap max-w-4xl">
            <div class="record_view-item">
                <div class="record_view-item_label">Company Name</div>
                <div class="record_view-item_value">Name</div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Parent Company</div>
                <div class="record_view-item_value">
                    Parent Company Name
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Active</div>
                <div class="record_view-item_value">
                    <button class="button-link align-middle">
                        <AdminIconCheck :active="true" />
                    </button>
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Inactive</div>
                <div class="record_view-item_value">
                    <AdminIconCheck :active="false" />
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Active with Help</div>
                <div class="record_view-item_value">
                    <button class="button-link align-middle">
                        <AdminIconCheck :active="true" />
                    </button>
                    <div class="text-sm text-gray-500">
                        Long help text next to an icon should wrap within the value column instead of pushing the
                        value below the label. Inactive records will only be accessible by admins.
                    </div>
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Address</div>
                <div class="record_view-item_value">
                    Address
                    123 Main Street<br>
                    Cityville, Province<br>
                    Zip Code: 12345<br>
                    Country: Imaginaria
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">
                    Email
                </div>
                <div class="record_view-item_value">
                    email@example.com
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">
                    Phone Number
                </div>
                <div class="record_view-item_value">
                    780-123-4756
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">
                    Empty value
                </div>
                <div class="record_view-item_value">
                    <span class="record_list-empty">Not complete</span>
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">
                    Loading
                </div>
                <div class="record_view-item_value">
                    <LoadingSpinner />
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Notes</div>
                <div class="record_view-item_value">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer suscipit elementum est,
                    in sodales sapien mollis quis. In erat nisl, hendrerit ac convallis ut, pellentesque at arcu.
                    Maecenas quam dolor, suscipit id mauris a, pretium maximus ipsum. Maecenas et dictum risus.
                    Cras elit neque, vestibulum eu euismod quis, luctus vitae justo.
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">HTML</div>
                <div class="record_view-item_value no_first_last_margin">
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer suscipit elementum est.</p>
                    <p>In sodales sapien mollis quis. In erat nisl, hendrerit ac convallis ut, pellentesque at arcu.
                        Maecenas quam dolor, suscipit id mauris a, pretium maximus ipsum. Maecenas et dictum risus.
                        Cras elit neque, vestibulum eu euismod quis, luctus vitae justo.</p>
                </div>
            </div>
            <div class="record_view-item_group_wrap">
                <h4 class="mt-2 mb-0 px-2">Group of Items</h4>
                <div class="record_view-item">
                    <div class="record_view-item_label">Item 1</div>
                    <div class="record_view-item_value">Value 1</div>
                </div>
                <div class="record_view-item">
                    <div class="record_view-item_label">Item 2</div>
                    <div class="record_view-item_value">Value 2</div>
                </div>
            </div>
        </div>

        <div class="record_view-item_wrap">
            <div class="record_view-header_wrap">
                <h3 class="m-0">Section Heading</h3>
                <div class="record_view-header_actions">
                    <a href="">Action Link</a>
                </div>
            </div>

            <table class="record_list-wrap hidden md:table">
                <thead class="record_list-headers">
                    <tr>
                        <th>Col 1</th>
                        <th>Col 2</th>
                        <th>Col Action</th>
                        <th class="text-center">Active</th>
                        <th class="text-center">Inactive</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="i in 3" :key="i" class="record_list-item">
                        <td class="record_list-col">
                            <a href="">Value 1-{{ i }}</a>
                        </td>
                        <td class="record_list-col">
                            Value 2-{{ i }}
                        </td>
                        <td class="record_list-col">
                            <a href="" class="block">Action 1</a>
                            <a href="" class="block">Action 2</a>
                        </td>
                        <td class="record_list-col">
                            <AdminIconCheck :active="true" />
                        </td>
                        <td class="record_list-col">
                            <AdminIconCheck :active="false" />
                        </td>
                    </tr>
                </tbody>
            </table>

            <ul class="record_list-wrap md:hidden">
                <li v-for="i in 3" :key="i" class="record_list-item">
                    <div class="record_list-col">
                        <div class="record_list-mobile_heading">Value 1</div>
                        <a href="">Value 1-{{ i }}</a>
                    </div>
                    <div class="record_list-col">
                        <div class="record_list-mobile_heading">Value 2</div>
                        <a href="">Value 2-{{ i }}</a>
                    </div>
                    <div class="record_list-col">
                        <a href="" class="block">Action 1</a>
                        <a href="" class="block">Action 2</a>
                    </div>
                    <div class="record_list-col">
                        <AdminIconCheck :active="true" />
                        <span class="record_list-mobile_heading">[True]</span>
                    </div>
                    <div class="record_list-col">
                        <AdminIconCheck :active="false" />
                        <span class="record_list-mobile_heading">[False]</span>
                    </div>
                </li>
            </ul>
        </div>

        <!-- ############################# -->
        <!-- ######### Alerts ############ -->
        <!-- ############################# -->
        <h2>Alerts</h2>
        <div class="alert alert-success">
            <div>Success Message!</div>
            <a href="" class="pl-4">×</a>
        </div>
        <div class="alert alert-info">
            <div>Information Message<br>With two lines</div>
            <a href="" class="pl-4">×</a>
        </div>
        <div class="alert alert-warning">
            <div>Warning Message<br>Don't do that</div>
            <a href="" class="pl-4">×</a>
        </div>
        <div class="alert alert-danger">
            <div>Danger Warning – You are going to break something. Donec ullamcorper nulla non metus auctor fringilla.
                Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec ullamcorper nulla non metus auctor
                fringilla. Donec sed odio dui. Donec ullamcorper nulla non metus auctor fringilla.
            </div>
            <a href="" class="pl-4">×</a>
        </div>

        <!-- ############################# -->
        <!-- ########## Modal ############ -->
        <!-- ############################# -->
        <h2>Modal</h2>
        <button type="button" class="button" @click="modalOpen = true">Open Modal</button>

        <AdminModal v-if="modalOpen" @closed="modalOpen = false">
            <template #default="{ close }">
                <h2 class="mt-0">Modal Heading</h2>
                <p>
                    Paragraph text with <a href="">a link</a>
                    and <button type="button" class="button-link">a button link</button>.
                </p>
                <div class="alert alert-info">
                    <div>An alert in a modal, with <a href="">a link</a>.</div>
                </div>
                <div class="field-wrap">
                    <label for="pattern-library-modal-text">Text field</label>
                    <ul class="field-errors">
                        <li>Error</li>
                    </ul>
                    <input id="pattern-library-modal-text" type="text" placeholder="Placeholder">
                    <div class="field-help">Use at least 8 characters.</div>
                </div>
                <div class="field-wrap">
                    <label for="pattern-library-modal-select">Select</label>
                    <select id="pattern-library-modal-select">
                        <option value="">Test 1</option>
                        <option value="">Test 2</option>
                    </select>
                </div>
                <div class="field-wrap field-wrap-checkbox">
                    <input id="pattern-library-modal-checkbox" type="checkbox">
                    <label for="pattern-library-modal-checkbox">Checkbox label</label>
                </div>
                <div class="mt-6">
                    <button type="button" class="button" @click="close">Save</button>
                    <button type="button" class="form-action button-link" @click="close">Cancel</button>
                </div>
            </template>
        </AdminModal>

        <AdminDelete record-desc="test" />
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useHead } from '@unhead/vue';
import VueMultiselect from 'vue-multiselect';
import FlatPickr from 'vue-flatpickr-component';
import FieldInput from '@/common/field_input.vue';
import FieldEmail from '@/common/field_email.vue';
import FieldTextarea from '@/common/field_textarea.vue';
import FieldFile from '@/common/field_file.vue';
import FieldRadios from '@/common/field_radios.vue';
import FieldCheckbox from '@/common/field_checkbox.vue';
import FieldCheckboxes from '@/common/field_checkboxes.vue';
import PlaceSuggestions from '@/common/place_suggestions.vue';
import RecordList from '@/common/record_list.vue';
import { initPatternLibraryStates } from '@/common/pattern_library_states';

useHead({
    title: 'Admin Pattern Library',
});

onMounted(initPatternLibraryStates);

const modalOpen = ref(false);

const buttonSections = [
    { heading: 'Buttons', class: null },
    { heading: 'Buttons on Dark Background', class: 'mt-4 p-4 bg-gray-800 text-on-dark-bg' },
];
// focus matches :focus & :focus-visible, as a keyboard focus does
const buttonStates = [
    { value: '', label: 'Default' },
    { value: 'hover', label: 'Hover' },
    { value: 'focus focus-visible', label: 'Focus' },
    { value: 'active', label: 'Active' },
];
/*
the Button & Critical Button rows should look like a BUTTON (the <a> is a link that looks like a button),
the Button Link rows like a LINK (the <button> is a button that looks like a link),
and the Link row is just for comparison
*/
const buttonRows = [
    { label: 'Button', class: 'button', tag: 'button' },
    { label: 'Button', class: 'button', tag: 'a' },
    { label: 'Button Disabled', class: 'button', tag: 'button', disabled: true },
    { label: 'Critical Button', class: 'button button-critical', tag: 'button' },
    { label: 'Critical Button', class: 'button button-critical', tag: 'a' },
    { label: 'Critical Button Disabled', class: 'button button-critical', tag: 'button', disabled: true },
    { label: 'Button Link', class: 'button-link', tag: 'button' },
    { label: 'Button Link', class: 'button-link', tag: 'a' },
    { label: 'Link', class: null, tag: 'a' },
];

const radios1Values = [ { value: 1, label: 'Option 1' }, { value: 2, label: 'Option 2' }, { value: 3, label: 'Option 3' } ];
const radios2Values = [
    { value: 1, label: 'Option 1' },
    { value: 2, label: 'Option 2' },
    { value: 3, label: 'Option 3' },
    { value: 4, label: 'Radio button Maecenas sed diam eget risus varius blandit sit amet non magna. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit.' },
];
const radiosPillsValues = [
    { value: 'yes', label: 'Yes' },
    { value: 'no', label: 'No' },
];

const textValue = ref();
const emailValue = ref();
const textareaValue = ref();
const fileValue = ref(null);
const imageValue = ref(null);
const existingFileValue = ref({ file: '#', originalFilename: 'existing-file.pdf' });
const notRemovableFileValue = ref({ file: '#', originalFilename: 'existing-file.pdf' });
const checkboxValue = ref(false);
const checkboxPillValue = ref(false);

const multiselectOptions = [ 'Option 1', 'Option 2', 'Option 3', 'Option 4', 'Option 5' ];
const multiselectSingleValue = ref(null);
const multiselectMultipleValue = ref([ 'Option 2', 'Option 3' ]);
const multiselectDisabledValue = ref('Option 1');

const dateValue = ref(null);
const dateConfig = { dateFormat: 'Y-m-d' };
const dateTimeValue = ref(null);
const dateTimeConfig = { dateFormat: 'Y-m-d H:i', enableTime: true };
const dateRangeValue = ref(null);
const dateRangeConfig = { dateFormat: 'Y-m-d', mode: 'range' };

const checkboxesValue = ref([ 2 ]);
const checkboxesValues = [ { value: 1, label: 'Item 1' }, { value: 2, label: 'Item 2' }, { value: 3, label: 'Item 3' } ];

const placeSuggestion = (placeId, mainText, secondaryText) => ({
    placePrediction: { placeId, mainText, secondaryText },
});
const placeSuggestions = [
    placeSuggestion('1', '10 Main Street', 'Cityville, AB, Canada'),
    placeSuggestion('2', '10 Main Street South', 'Townsville, AB, Canada'),
    placeSuggestion('3', '10 Main Avenue', 'Villageton, SK, Canada'),
];
const placeSuggestionsActiveIndex = ref(0);

const recordListItems = [
    {
        col1: 'Value 1',
        col2: 'Value 2',
        col3: 'Action 1',
        col4: 'Nullam quis risus eget urna mollis ornare vel eu leo. Nullam quis risus eget urna mollis ornare vel eu leo.',
        col5: true,
        col6: false,
    },
    {
        col1: 'Value 1',
        col2: 'Value 2',
        col3: 'Action 1',
        col4: 'Nullam quis risus eget urna mollis ornare vel eu leo. Nullam quis risus eget urna mollis ornare vel eu leo.',
        col5: true,
        col6: false,
    },
    {
        col1: 'Value 1',
        col2: 'Value 2',
        col3: 'Action 1',
        col4: 'Nullam quis risus eget urna mollis ornare vel eu leo. Nullam quis risus eget urna mollis ornare vel eu leo.',
        col5: true,
        col6: false,
    },
];

const recordListActionItems = [
    {
        id: 1,
        name: 'Form Builder',
        expiryDate: '2026-10-26',
        renewed: false,
        outstanding: false,
        client: 'Northern Lights Development Group',
        price: '$150.00',
        active: true,
    },
    {
        id: 2,
        name: 'SEO Toolkit',
        expiryDate: '2026-12-05',
        renewed: false,
        outstanding: false,
        client: 'Prairie Fuel Distributors Ltd.',
        price: '$35.00',
        active: true,
    },
    {
        id: 3,
        name: 'Shipping Rates Plugin',
        expiryDate: '2027-11-03',
        renewed: true,
        outstanding: false,
        client: 'Riverside Trucking Ltd.',
        price: '$150.00',
        active: true,
    },
    {
        id: 4,
        name: 'Shipping Rates Plugin',
        expiryDate: '2028-02-11',
        renewed: true,
        outstanding: true,
        client: 'Jane Smith',
        price: '$150.00',
        active: false,
    },
];
const selectedRecordIds = ref([ 2 ]);
</script>
