/**
 * Shows interaction states side by side in the pattern library, without shipping any state
 * classes in the production CSS. Every rule using one of STATES is copied with the
 * pseudo-class swapped for [data-pl-state~="…"], so <button data-pl-state="hover"> looks
 * hovered. Each copy goes straight after its original, keeping its layer, specificity &
 * source order, so it cascades exactly like the real state. The exception is a copy taken out
 * of @media (hover: hover), which goes after the whole media block: a later rule of equal
 * specificity inside that block would beat the real state but not the copy.
 */

import { logError } from './lib';

const STATES = ['hover', 'focus-visible', 'focus-within', 'focus', 'active'];

// the lookbehind skips Tailwind variant class names, e.g. .hover\:underline
const PSEUDO_CLASS = new RegExp(`(?<!\\\\):(${STATES.join('|')})(?![\\w-])`, 'g');

// unwrapped so the states also show on touch devices
const HOVER_MEDIA = /^\(hover:\s*hover\)$/;

const processedSheets = new WeakSet();

const replaceStates = (cssText) => cssText.replace(PSEUDO_CLASS, '[data-pl-state~="$1"]');

// copied with any nested rules; nested states also get copies of their own via processRules()
const copyStyleRule = (rule) => {
    const selector = replaceStates(rule.selectorText);
    if (selector === rule.selectorText) {
        return null;
    }

    return selector + rule.cssText.slice(rule.selectorText.length);
};

const insertAfter = (container, index, cssText) => {
    try {
        container.insertRule(cssText, index + 1);
    } catch (e) {
        logError(e);
    }
};

// walks backwards so inserted copies aren't visited
const processRules = (container) => {
    for (let i = container.cssRules.length - 1; i >= 0; i--) {
        const rule = container.cssRules[i];

        if (rule instanceof CSSMediaRule && HOVER_MEDIA.test(rule.conditionText)) {
            [...rule.cssRules]
                .map((child) => [child.cssText, replaceStates(child.cssText)])
                .filter(([original, copy]) => copy !== original)
                .reverse()
                .forEach(([, copy]) => insertAfter(container, i, copy));

            continue;
        }

        if (rule instanceof CSSStyleRule) {
            const copy = copyStyleRule(rule);
            if (copy) {
                insertAfter(container, i, copy);
            }
        }

        // style rules have nested rules in dev; the build flattens them
        if (rule.cssRules) {
            processRules(rule);
        }
    }
};

// call on mount: a route's CSS can load after this module, & each new stylesheet is processed once
export function initPatternLibraryStates () {
    for (const sheet of document.styleSheets) {
        if (processedSheets.has(sheet)) {
            continue;
        }

        try {
            processRules(sheet);
            processedSheets.add(sheet);
        } catch {
            // cross-origin sheets (e.g. web fonts) can't be read
        }
    }
}

// a CSS update in dev replaces the stylesheet, dropping the copies
import.meta.hot?.on('vite:afterUpdate', initPatternLibraryStates);
