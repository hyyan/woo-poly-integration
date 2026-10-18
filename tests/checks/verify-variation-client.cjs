// Run with the installed WooCommerce assets/js/admin/meta-boxes-product-variation.js.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

assert.ok(process.argv[2], 'Supply the installed WooCommerce variation script path');
let source = fs.readFileSync(process.argv[2], 'utf8');
// Expose the native actions without initializing unrelated editor controls.
source = source.replace(/wc_meta_boxes_product_variations_(actions|media|ajax|pagenav)\.init\(\);/g, '');
assert.match(source, /\}\s*\);\s*$/);
source = source.replace(/\}\s*\);\s*$/, 'globalThis.nativeActions = wc_meta_boxes_product_variations_ajax; });');
let submitted;
const button = {};
function jquery(element) {
    assert.equal(element, button);
    return { attr(name) { assert.equal(name, 'rel'); return '123'; } };
}
jquery.post = (url, payload) => { submitted = { url, payload }; };
const context = {
    wp: { media: { model: { settings: { post: { id: 1 } } } } },
    jQuery: callback => callback(jquery),
    window: { confirm: () => true, wcTracks: { recordEvent() {} } },
    woocommerce_admin_meta_boxes_variations: {
        delete_variations_nonce: 'fixture-deletion-nonce',
        ajax_url: '/wp-admin/admin-ajax.php',
        i18n_remove_variation: 'Remove this variation?'
    }
};
vm.runInNewContext(source, context);
context.nativeActions.check_for_changes = () => {};
context.nativeActions.block = () => {};
context.nativeActions.remove_variation.call(button);
assert.equal(submitted.url, '/wp-admin/admin-ajax.php');
assert.equal(submitted.payload.action, 'woocommerce_remove_variations');
assert.equal(submitted.payload.security, 'fixture-deletion-nonce');
assert.deepEqual(Array.from(submitted.payload.variation_ids), ['123']);
console.log('PASS: native WooCommerce removal submits variation_ids and its localized deletion nonce');
