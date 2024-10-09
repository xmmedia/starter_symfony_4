import { zxcvbnOptions } from '@zxcvbn-ts/core';
import * as zxcvbnCommonPackage from '@zxcvbn-ts/language-common';
import * as zxcvbnEnPackage from '@zxcvbn-ts/language-en';

let zxcvbnInstalled = false;

export const install = () => {
    if (zxcvbnInstalled) {
        return;
    }

    zxcvbnOptions.setOptions({
        translations: zxcvbnEnPackage.translations,
        graphs: zxcvbnCommonPackage.adjacencyGraphs,
        useLevenshteinDistance: true,
        dictionary: {
            ...zxcvbnCommonPackage.dictionary,
            ...zxcvbnEnPackage.dictionary,
        },
    });

    zxcvbnInstalled = true;
};
