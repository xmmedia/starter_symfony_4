export default class BaseClass {
    constructor () {
    }

    addGetters (keys, dataKey) {
        for (const key of keys) {
            if (!(key in this)) {
                Object.defineProperty(this, key, {
                    enumerable: true,
                    get: () => {
                        return this[dataKey][key];
                    },
                });
            }
        }
    }

    hasKeys (requiredKeys, object = this) {
        return requiredKeys.every((key) => key in object && undefined !== object[key]);
    }

    throwIfMissingKeys (requiredKeys, object = this) {
        if (!this.hasKeys(requiredKeys, object)) {
            const missingKeys = requiredKeys.filter((key) => {
                return !this.hasKeys([key], object);
            });

            throw new ReferenceError('Keys missing in class/object.\nMissing key(s): '+missingKeys.join(', ')+'\nRequired key(s): '+requiredKeys.join(', ')+'\nIncluded in class: '+Object.keys(object).join(', '));
        }
    }
}
