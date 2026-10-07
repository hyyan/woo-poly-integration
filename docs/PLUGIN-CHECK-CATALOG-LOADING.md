# Bundled catalog loader compatibility evidence

The installed warning PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound at src/Hyyan/WPI/Plugin.php:77 remains open. No suppression or exception has been accepted.

An isolated native WordPress bootstrap removes only the plugin loadTextDomain callback at plugins_loaded priority zero. A probe records the actual Spanish Features translation at priority 99, before init, and again after the normal bootstrap and manual registration. Both labs retain their normal active dependencies. The probes only apply when the owned CLI process defines its probe constant. They change no stored locale setting and are removed after execution.

| WordPress | Plugin loader | Translation after plugins_loaded | Translation after normal bootstrap |
| --- | --- | --- | --- |
| 7.1.3 | normal | Características | Características |
| 7.1.3 | skip | Características | Características |
| 5.4.2 | normal | Características | Características |
| 5.4.2 | skip | Features | Características |

Current WordPress reads Text Domain and Domain Path headers in wp-settings.php and registers the bundled path before including the plugin. On WordPress 5.4.2, lazy loading only finds installed global language packs. The existing plugin loader also falls back to the bundled languages directory. Removing it loses the bundled Spanish translation during the supported older bootstrap. A later get_plugin_data call loads that catalog again, which masked this loss in the first probe that observed only the completed bootstrap.

The final probe confirms that removal succeeded in both skip runs. It preserves the native bundled Spanish catalog path and does not substitute a synthetic translation. The plugin also exposes loadTextDomain as a public method. Its existing timing and catalog behavior remain unchanged. The precise remaining warning requires acceptance if this compatibility contract is retained. The report does not count retention as a fix.

Evidence: evidence/plugin-check-catalog-loading-probe.json and evidence/plugin-check-catalog-loading-probe.txt.
