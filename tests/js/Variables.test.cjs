const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const savingKey = 'woo-poly-integration-variable-language';

function editor(asset, mode = 'classic') {
    const elements = new Map();
    const values = new Map([['#product-type', 'simple'], ['#post_lang_choice', 'de']]);
    const locks = new Set();
    const state = { disabled: false, dialogVisible: false, dialogOptions: null };
    const actions = {
        lockPostSaving(key) { locks.add(key); },
        unlockPostSaving(key) { locks.delete(key); }
    };
    const window = {};
    if (mode !== 'classic') {
        window.wp = { data: { dispatch(store) {
            assert.equal(store, 'core/editor');
            return mode === 'block' ? actions : null;
        } } };
    }
    function jquery(selector) {
        if (typeof selector === 'function') { selector(); return; }
        if (typeof selector !== 'string') { return selector; }
        if (!elements.has(selector)) {
            elements.set(selector, {
                listeners: new Map(),
                on(event, callback) { this.listeners.set(event, callback); return this; },
                val() { return values.get(selector); },
                prop(name, value) { assert.equal(name, 'disabled'); state.disabled = value; return this; },
                html() { return this; },
                attr() { return this; },
                appendTo() { return this; },
                dialog(value) {
                    if (typeof value === 'object') { state.dialogOptions = value; state.dialogElement = this; }
                    else { state.dialogVisible = value === 'open'; }
                    return this;
                }
            });
        }
        return elements.get(selector);
    }
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/js', asset), 'utf8'), {
        jQuery: jquery, document: {}, window,
        HYYAN_WPI_VARIABLES: { defaultLang: 'en', title: 'Wrong language', content: 'Create in the default language' }
    }, { filename: asset });
    return {
        state, locks,
        change(selector, value) {
            values.set(selector, value);
            elements.get(selector).listeners.get('change')();
        },
        closeWarning() {
            state.dialogOptions.buttons[0].click.call(state.dialogElement);
        }
    };
}

for (const asset of ['Variables.js', 'Variables.min.js']) {
    test(`${asset}: classic editor blocks invalid language and restores valid choices`, () => {
        const instance = editor(asset);
        instance.change('#product-type', 'variable');
        assert.equal(instance.state.dialogVisible, true);
        assert.equal(instance.state.disabled, true);
        instance.closeWarning();
        assert.equal(instance.state.dialogVisible, false);
        assert.equal(instance.state.disabled, true);
        instance.change('#post_lang_choice', 'en');
        assert.equal(instance.state.disabled, false);
    });
    test(`${asset}: block editor locks saving after warning dismissal`, () => {
        const instance = editor(asset, 'block');
        instance.change('#product-type', 'variable');
        assert.equal(instance.locks.has(savingKey), true);
        instance.closeWarning();
        assert.equal(instance.locks.has(savingKey), true);
        instance.change('#post_lang_choice', 'en');
        assert.equal(instance.locks.has(savingKey), false);
    });
    test(`${asset}: changing product type releases only the plugin lock`, () => {
        const instance = editor(asset, 'block');
        instance.locks.add('core-validation');
        instance.change('#product-type', 'variable');
        assert.equal(instance.locks.has(savingKey), true);
        instance.change('#product-type', 'simple');
        assert.equal(instance.locks.has(savingKey), false);
        assert.equal(instance.locks.has('core-validation'), true);
        assert.equal(instance.state.disabled, false);
    });
    test(`${asset}: missing editor store preserves classic behavior`, () => {
        const instance = editor(asset, 'missing-store');
        instance.change('#product-type', 'variable');
        assert.equal(instance.state.disabled, true);
        instance.change('#post_lang_choice', 'en');
        assert.equal(instance.state.disabled, false);
    });
}
