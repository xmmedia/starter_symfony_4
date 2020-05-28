import { maxLength, minLength, required } from 'vuelidate/lib/validators';

export default function (template, edit) {
    const validation = {
        title: {
            required,
            minLength: minLength(5),
            maxLength: maxLength(191),
        },
    };

    if (!edit) {
        // when adding
        validation.path = {
            required,
            maxLength: maxLength(191 - 1),
            // @todo duplicate
        };
        validation.template = {
            required,
        };
    }

    if (template) {
        validation.content = {};

        if (template.editMetaDescription) {
            validation.content.metaDescription = {
                minLength: minLength(10),
                maxLength: maxLength(180),
            };
        }

        for (const item of template.items) {
            const itemValidation = {};

            if (item.required) {
                itemValidation.required = required;
            }
            if (item.config.min) {
                itemValidation.minLength = minLength(item.config.min);
            }
            if (item.config.max) {
                itemValidation.maxLength = maxLength(item.config.max);
            }

            validation.content[item.item] = itemValidation;
        }
    }

    return validation;
}
