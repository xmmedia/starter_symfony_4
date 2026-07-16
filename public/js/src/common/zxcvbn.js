import { ZxcvbnFactory } from '@zxcvbn-ts/core';
import * as zxcvbnCommonPackage from '@zxcvbn-ts/language-common';
import * as zxcvbnEnPackage from '@zxcvbn-ts/language-en';

let zxcvbnInstance;

export const getZxcvbn = () => {
    if (!zxcvbnInstance) {
        zxcvbnInstance = new ZxcvbnFactory({
            translations: zxcvbnEnPackage.translations,
            graphs: zxcvbnCommonPackage.adjacencyGraphs,
            useLevenshteinDistance: true,
            dictionary: {
                ...zxcvbnCommonPackage.dictionary,
                ...zxcvbnEnPackage.dictionary,
            },
        });
    }

    return zxcvbnInstance;
};
