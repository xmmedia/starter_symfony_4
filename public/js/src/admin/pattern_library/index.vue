<template>
    <div class="max-w-3xl">
        <!-- ############################# -->
        <!-- ####### Headings ######### -->
        <!-- ############################# -->
        <h2>Headings</h2>
        <h1>Heading 1</h1>
        <h2>Heading 2</h2>
        <h3>Heading 3</h3>
        <h4>Heading 4</h4>
        <h5>Heading 5</h5>
        <h6>Heading 6</h6>

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

        <!-- ############################# -->
        <!-- ######### Buttons ########### -->
        <!-- ############################# -->
        <h2>Buttons</h2>
        <button class="button">Button Button</button>
        <a href="" class="button">Link Button</a>
        <a href="" class="button button-critical">Delete Button</a>
        <div class="flex my-4">
            <button class="button-link">Button Link</button>
            <a href="" class="ml-4">Link</a>
        </div>
        <div style="background: grey; margin-top: 1rem; padding: 1rem;">
            <h2>Buttons on Dark Background</h2>
            <button class="button">Button Button</button>
            <a href="" class="button">Link Button</a>
            <a href="" class="button button-critical bg-gray-300">Delete Button</a>
        </div>

        <!-- ############################# -->
        <!-- ########## Forms ############ -->
        <!-- ############################# -->
        <!--
        IMPORTANT
        Spaces between inputs and labels that are inline with each other (beside)
        cause issues with formatting. Leave the HTML formatting as is!
        Use the dashes in the Twig output tags, such as `{{- var -}}`
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
                    <textarea id="pattern-library-textarea" class="h-32"></textarea>
                </div>
                <div class="field-wrap">
                    <label for="pattern-library-select">Select</label>
                    <select id="pattern-library-select">
                        <option value="">Test 1</option>
                        <option value="">Test 2</option>
                    </select>
                </div>
                <div class="field-wrap-checkbox">
                    <input id="pattern-library-checkbox"
                           type="checkbox"
                           name="pattern-library-checkbox">
                    <label for="pattern-library-checkbox">Checkbox label</label>
                </div>
                <fieldset class="field-wrap-radios">
                    <legend>Horizontal radios</legend>
                    <div class="field-wrap-radio inline">
                        <input id="pattern-library-radio-01"
                               type="radio"
                               name="pattern-library-radio-1">
                        <label for="pattern-library-radio-01">Option 1</label>
                    </div>
                    <div class="field-wrap-radio inline">
                        <input id="pattern-library-radio-02"
                               type="radio"
                               name="pattern-library-radio-1">
                        <label for="pattern-library-radio-02">Option 2</label>
                    </div>
                    <div class="field-wrap-radio inline">
                        <input id="pattern-library-radio-03"
                               type="radio"
                               name="pattern-library-radio-1">
                        <label for="pattern-library-radio-03">Option 3</label>
                    </div>
                </fieldset>

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
                    <div class="field-help">Some help…</div>
                </fieldset>

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

                <fieldset class="mt-6 mb-4 p-4 border border-gray-400">
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

                <div class="mt-6 mb-4 p-4 pt-3 border border-gray-400">
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

                <div>
                    <button class="button">Primary Button</button>
                    <button class="form-action button-link">Cancel Link</button>
                    <a href="" class="form-action">Go Back</a>
                    <div class="field-help">Form button help.</div>

                    <ul class="form-extra_actions">
                        <li>
                            <button class="form-action button-link">Delete</button>
                        </li>
                        <li>
                            <a href="" class="form-action">Other Action</a>
                        </li>
                    </ul>
                </div>

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
                    <div class="button_pair-inactive">&lt;</div>
                    <div class="button_pair-inactive">&gt;</div>
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
                    <div class="button_pair-inactive">&lt;</div>
                    <a href="" class="button_pair-button">&gt;</a>
                </div>
            </div>
        </div>

        <div class="header-wrap">
            <div class="header-page_title">Left Active</div>
            <div class="header-actions">
                <div class="button_pair-wrap header-button_pair">
                    <a href="" class="button_pair-button">&lt;</a>
                    <div class="button_pair-inactive">&gt;</div>
                </div>
            </div>
        </div>

        <!-- ############################# -->
        <!-- ####### Record List ######### -->
        <!-- ############################# -->
        <h2>Record List</h2>
        <div class="record_list-record_count">Showing X of X</div>
        <ul class="record_list-wrap">
            <li class="record_list-headers">
                <div class="record_list-col">Col 1</div>
                <div class="record_list-col">Col 2</div>
                <div class="record_list-col text-center">Col 2</div>
                <div class="record_list-col"></div>
            </li>

            <li v-for="i in 3" :key="i" class="record_list-item">
                <div class="record_list-col">
                    <a href="">Value 1-{{ i }}</a>
                </div>
                <div class="record_list-col">
                    Value 2-{{ i }}
                </div>
                <div class="record_list-col lg:text-center">
                    <svg class="record_list-icon text-green-600" width="20" height="20">
                        <use :xlink:href="iconsPath+'#check'"></use>
                    </svg>
                    <span class="record_list-mobile_heading">[True]</span>
                </div>
                <div class="record_list-col record_list-col-actions">
                    <a href="" class="record_list-action">Action 1</a>
                    <a href="" class="record_list-action">Action 2</a>
                </div>
            </li>

            <li class="record_list-item">
                <div class="record_list-col">
                    <a href="">Long Value</a>
                </div>
                <div class="record_list-col">
                    Nullam quis risus eget urna mollis ornare vel eu leo.
                    Nullam quis risus eget urna mollis ornare vel eu leo.
                </div>
                <div class="record_list-col lg:text-center">
                    <svg class="record_list-icon fill-current text-green-600" width="20" height="20">
                        <use :xlink:href="iconsPath+'#check'"></use>
                    </svg>
                    <span class="record_list-mobile_heading">[True]</span>
                </div>
                <div class="record_list-col record_list-col-actions">
                    <a href="" class="record_list-action">Action 1</a>
                    <a href="" class="record_list-action">Action 2</a>
                </div>
            </li>

            <li class="record_list-item record_list-item-inactive">
                <div class="record_list-col">
                    <a href="">Inactive Value 1</a>
                </div>
                <div class="record_list-col">
                    Inactive Value 2
                </div>
                <div class="record_list-col lg:text-center">
                    <svg class="record_list-icon fill-current text-gray-800" width="20" height="20">
                        <use :xlink:href="iconsPath+'#check'"></use>
                    </svg>
                    <span class="record_list-mobile_heading">[True]</span>
                </div>
                <div class="record_list-col record_list-col-actions">
                    <a href="" class="record_list-action">Action 1</a>
                    <a href="" class="record_list-action">Action 2</a>
                </div>
            </li>
        </ul>

        <!-- ############################# -->
        <!-- ####### Record View ######### -->
        <!-- ############################# -->
        <h2>Record View</h2>
        <div class="record_view-item_wrap">
            <div class="record_view-item">
                <div class="record_view-item_label">Name</div>
                <div class="record_view-item_value">John Smith</div>
            </div>

            <div class="record_view-item">
                <div class="record_view-item_label">Is True</div>
                <div class="record_view-item_value">
                    <span title="A Checkmark">
                        <svg class="record_view-icon text-green-600" width="20" height="20">
                            <use :xlink:href="iconsPath+'#check'"></use>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="record_view-item">
                <div class="record_view-item_label">Is False</div>
                <div class="record_view-item_value record_view-item_value-inactive">
                    <em>Not Active</em>
                </div>
            </div>

            <div class="record_view-item">
                <div class="record_view-item_label">List</div>
                <div class="record_view-item_value">
                    <ul class="pl-4">
                        <li>Item 1</li>
                        <li>Item 2</li>
                        <li>Item 3</li>
                    </ul>
                </div>
            </div>

            <div class="record_view-item">
                <div class="record_view-item_label">Multiline</div>
                <div class="record_view-item_value">
                    Nullam id dolor id nibh ultricies vehicula ut id elit.<br>
                    Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh,
                    ut fermentum massa justo sit amet risus.<br>
                    Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit.
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

            <ul class="record_list-wrap">
                <li class="record_list-headers">
                    <div class="record_list-col record_list-col-view">Col 1</div>
                    <div class="record_list-col record_list-col-view">Col 2</div>
                    <div class="record_list-col record_list-col-view">Col 3</div>
                    <div class="record_list-col record_list-col-view"></div>
                </li>

                <li v-for="i in 5" :key="i" class="record_list-item">
                    <div class="record_list-col record_list-col-view">
                        <div class="record_list-mobile_heading">Col 1</div>
                        Value 1-{{ i }}
                    </div>
                    <div class="record_list-col record_list-col-view">
                        <div class="record_list-mobile_heading">Col 2</div>
                        Value 2-{{ i }}
                    </div>
                    <div class="record_list-col record_list-col-view">
                        <div class="record_list-mobile_heading">Col 3</div>
                        Value 3-{{ i }}
                    </div>
                    <div class="record_list-col record_list-col-actions record_list-col-view">
                        <a href="" class="record_list-action">Action</a>
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

        <admin-delete record-desc="test" />
    </div>
</template>

<script>
import iconsPath from '@/../../images/icons-admin.svg';

export default {
    metaInfo: {
        title: 'Admin Pattern Library',
    },

    data () {
        return {
            iconsPath,
        };
    },
}
</script>
