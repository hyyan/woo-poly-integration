# Email languages

To translate customer emails, enable **Settings → WooPoly → Features → Emails**, then edit the email text under **WooCommerce → Settings → Emails**. Translate customized text under **Languages → Translations**, in the WooCommerce Emails group. Default email text uses the installed WooCommerce language packs. Test an order in each store language and check its customer emails.

Emails and translation is a more complex than it looks: both to determine the right language and to ensure that everything in the email is translated.

This article considers historic deficiencies and intrinsic limitations in the plugin and includes important hints for plugin developers looking for maximum translation compatibility.

## Language vs Locale
Language is not the same as Locale:
* Language English by language code or slug 'en' is used in Polylang to tie post (and products and orders) to languages and is the default format returned from the Polylang api
* Locale ie US-English (en_US) or UK-English (en_GB) is more specific than language and may affect:
  * WordPress Translation files
  * Date formats
  * Number formats - there's an option in this plugin under Settings, WooPoly, Use locale number formats to show prices etc in the locale number format of the user interface. This option is enabled by default; you can turn it off if you need a different formatting convention.

Although there may be only one version of a language in Polylang, the WordPress language switching is always by locale to pick up the correct language files.
To review the installed languages see the Polylang admin page Language, Languages on the admin menu: /wp-admin/admin.php?page=mlang


## Multiple current languages - which language should the email be sent in?

There are many different possible language variables:
1. Order language - `pll_get_post_language` for the order id is the preferred option where order is is known
1. User language - `get_user_locale( $user_id )` is preferred if there is no order id but the email is about a customer and the customer user id is known (eg reset password email)
1. Polylang current language - this turns out not be useful to determine current language because the current language may not be set as expected by the end user, for example if the email is sent from an admin screen listing orders or users in all languages or from an IPN payment notification or other api call for example where woocommerce_defer_transactional_emails is set so that the email is trigged by cron rather than running in user context. 
1. WordPress `get_locale()` this actually used to set the current language rather than get the current language, by applying filter to 'locale' and 'plugin_locale' so that subsequent calls to standard functions like `$woocommerce->load_plugin_textdomain();` which use get_locale() internally will get the locale can be co-erced into loading the correct language for the email.
1. WordPress determine_locale() - returns user language if in Wordpress Admin otherwise returns get_locale() but also has its own filter and is also used by `$woocommerce->load_plugin_textdomain();`
1. WordPress Base language - `( is_multisite() ) ? get_site_option( 'WPLANG' ) : get_option( 'WPLANG' );` - normally use get_locale() but this will be filtered as above

For reliability this plugin will only use the first two to set the target language to avoid having to guess the most desirable language from context.
It may however necessary to check the current language to determine whether a switch is necessary and to ensure the switch only happens once. 

### woocommerce_defer_transactional_emails
At time of writing is set to false by default but can be filtered to turn on email background processing.


## 3 Translation Mechanisms - how to translate everything?

As per [Translation Overview](/translations/overview.md) there are three different types of translations:

1. WordPress translation mechanism:  this uses language files for WordPress and each plugin, each language containing dictionaries of texts used for each plugin. The core framework takes care of downloading the files for each language, and additionally the translations can be customised by adding custom language files, typically using Loco Translate.  However to switch languages fully (to send emails in a different language) it is necessary to unload previously loaded language files and re-load new language version of the files. This plugin will only unload language files for WooCommerce so if additional plugins are adding text to emails, for compatibility they should hook the language switching actions  provided by this plugin as per the illustration below for woo-advanced-shipment-tracking:

```
/*
 * when switching language for order email, unload these additional text domains
 */
function my_unload_email_textdomains() {
	unload_textdomain( 'woo-advanced-shipment-tracking' );
}

add_action( 'woo-poly.Emails.switchLanguage', 'my_unload_email_textdomains' );
/*
 * when switching language for order email, reload these additional text domains
 */
function my_load_email_textdomains() {
	global $WC_advanced_Shipment_Tracking;
	if ( $WC_advanced_Shipment_Tracking ) {
		$WC_advanced_Shipment_Tracking->wst_load_textdomain();
	}
}

add_action( 'woo-poly.Emails.afterSwitchLanguage', 'my_load_email_textdomains' );
```

2. String values set in admin screens
These can be set in one of two ways:
* by default no value is set and the translation mechanism used is the WordPress translation files mechanism.  This is true for WordPress but not the case for some other plugins which will populate the default values to the options table on first installation.
* when a value is set, it is saved to and translated at Polylang, Languages, Strings Translations, WooCommerce Emails /wp-admin/admin.php?page=mlang_strings&s&group=WooCommerce+Emails

3. Data Items
Most obviously Products but also all the associated meta and taxonomy data should normally be displayed in the same language as the rest of the email.


## WooCommerce Email firing
### setup_locale() and filters 'woocommerce_email_setup_locale' and 'woocommerce_email_restore_locale' 
When triggering and email the first thing WooCommerce does is to call setup_locale(), however this does not have an override and the only filter is 'woocommerce_email_setup_locale' which does not include any parameters to indicate which email is to be sent, about which object.
This cannot be used to switch an email into the user language (which is the objective of this plugin) but in fact only could be used to force a user email to be switched to the site base locale.  Although this uses `wc_switch_to_site_locale()` which could be co-erced into switching into a different language by filtering get_locale() into the order locale for example, the lack of context on these filters makes it difficult to do reliably.
The plugin uses the WooCommerce setup and restore locale events to preserve the caller's language context. It switches to the order or customer language when the email recipient, subject, heading, or content is processed, then restores the previous context after sending. Do not disable these hooks in custom email code.

Next the email class will load the order and attempt to send it using code like:
```
if ( $this->is_enabled() && $this->get_recipient() ) {
  $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
}
```
$this->get_recipient() is therefore the first call which has the full context about the email which is to be sent as has a filter like this:
```
$recipient  = apply_filters( 'woocommerce_email_recipient_' . $this->id, $this->recipient, $this->object, $this );
```
* the id is the id of the email as in new_order, new_account, reset_password etc
* The object is the underlying object for the email - the order or user account the email is about
with this information we can determine the correct language for the email.
Obviously this and the other calls are called multiple times with the same parameters and we only want to switch language once.
