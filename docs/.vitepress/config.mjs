import { defineConfig } from 'vitepress';

export default defineConfig({
    title: 'WooPoly',
    description: 'A practical guide to running a multilingual WooCommerce store with Polylang.',
    base: '/woo-poly-integration/',
    lang: 'en',
    themeConfig: {
        siteTitle: 'WooPoly',
        nav: [
            { text: 'Guide', link: '/getting-started/' },
            { text: 'Support', link: 'https://github.com/hyyan/woo-poly-integration/issues' }
        ],
        socialLinks: [
            { icon: 'github', link: 'https://github.com/hyyan/woo-poly-integration' }
        ],
        search: { provider: 'local' },
        outline: { level: [2, 3], label: 'On this page' },
        sidebar: [
            {
                text: 'Start here',
                items: [
                    { text: 'Overview', link: '/' },
                    { text: 'Installation', link: '/getting-started/installation' },
                    { text: 'Getting started', link: '/getting-started/' }
                ]
            },
            {
                text: 'Store settings',
                items: [
                    { text: 'Features', link: '/settings/features' },
                    { text: 'Metadata synchronization', link: '/settings/metadata' }
                ]
            },
            {
                text: 'Products',
                items: [
                    { text: 'Categories', link: '/products/categories' },
                    { text: 'Tags', link: '/products/tags' },
                    { text: 'Attributes', link: '/products/attributes' },
                    { text: 'Stock synchronization', link: '/products/stock' },
                    { text: 'Pricing and currencies', link: '/products/pricing' },
                    { text: 'Price localization', link: '/products/price-localization' },
                    { text: 'Imports and bulk editing', link: '/products/import-and-bulk-edit' },
                    { text: 'Custom fields', link: '/products/custom-fields' }
                ]
            },
            {
                text: 'Translations',
                items: [
                    { text: 'How translations work', link: '/translations/overview' },
                    { text: 'Translating store text', link: '/translations/strings' },
                    { text: 'Email languages', link: '/translations/emails' }
                ]
            },
            {
                text: 'Further help',
                items: [
                    { text: 'Performance', link: '/guides/performance' }
                ]
            },
            {
                text: 'Advanced examples',
                collapsed: true,
                items: [
                    { text: 'Locale switching', link: '/advanced/locale-switching' },
                    { text: 'Variation integrations', link: '/advanced/variation-examples' }
                ]
            }
        ],
        editLink: {
            pattern: 'https://github.com/hyyan/woo-poly-integration/edit/main/docs/:path',
            text: 'Improve this page'
        },
        footer: {
            message: 'Released under the <a href="https://github.com/hyyan/woo-poly-integration/blob/main/LICENSE">MIT License</a>.',
            copyright: 'Hyyan Abo Fakher'
        }
    }
});
