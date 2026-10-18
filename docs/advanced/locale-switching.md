# Locale switching example

This is an advanced example for a developer maintaining a third-party integration. Store owners do not need to add this code to translate WooCommerce emails. Use the plugin's [email settings](/translations/emails.md) first.

Here's a simplified example which might help if your plugin needs to switch text domain.
(happens eg if system needs to send out email in user locale / order language instead of system language etc)
This is not how this plugin does it but I verified something similar to this on another plugin:

```
class myClass
{

    private $pluginlocale;

    public function __construct()
    {
        add_action('plugins_loaded', array(__CLASS__, 'load_textdomain'));
        //locale management for translations
        add_action('switch_locale', array($this, 'my_switch_locale'), 10, 1);
        add_filter('plugin_locale', array($this, 'my_correct_locale'), 100, 2);
    }

    /**
     * load translations
     */
    public static function load_textdomain()
    {
        load_plugin_textdomain('my-text-domain', false, 'my-plugin/languages/');
    }

    /**
     * Fires when the locale is switched.
     *
     * @since 4.7.0
     *
     * @param string $locale The new locale.
     */
    public function my_switch_locale($locale)
    {
        $this->pluginlocale = $locale;
        $this->load_textdomain();
    }

    /**
     * Filters a plugin's locale.
     *
     * @since 3.0.0
     *
     * @param string $locale The plugin's current locale.
     * @param string $domain Text domain. Unique identifier for retrieving translated strings.
     */
    public function my_correct_locale($locale, $domain)
    {
        if ($this->pluginlocale) {
            return $this->pluginlocale;
        } else {
            return $locale;
        }
    }
}
```

The actual locale switch might happen like this:
```
            if (isset($order) && $order instanceof WC_Order && function_exists('pll_get_post_language')) {
                $locale = pll_get_post_language($order->get_id(), 'locale'); 
                $switched = switch_to_locale($locale);
                try {
                    // Render the integration's translated content here.
                } finally {
                    if ($switched) {
                        restore_previous_locale();
                    }
                }
            }
```
Unfortunately load_textdomain() doesn't take a locale parameter, but it does do a filter which is why you need to also add_filter('plugin_locale', array($this, 'my_correct_locale')) to tell load_textdomain() which locale to use, if it is not the default.
