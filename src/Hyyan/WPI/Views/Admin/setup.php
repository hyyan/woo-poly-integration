<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Hyyan\WPI\Admin\Setup;
use Hyyan\WPI\Admin\SetupPages;
use Hyyan\WPI\Plugin;

if (!defined('ABSPATH')) {
    exit('restricted access');
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included inside Setup::outputPage(); template variables are local to that method.
?>
<div class="wrap wpi-setup">
    <header class="wpi-setup-header">
        <div>
            <p class="wpi-eyebrow"><?php esc_html_e('WOOPOLY · GETTING STARTED', 'woo-poly-integration'); ?></p>
            <h1><?php esc_html_e('A store in every language.', 'woo-poly-integration'); ?></h1>
            <p class="wpi-intro"><?php esc_html_e('Connect your plugins, choose your languages, and prepare your store pages. Pick up where you left off, any time.', 'woo-poly-integration'); ?></p>
        </div>
		<a class="button" href="<?php echo esc_url( \Hyyan\WPI\Admin\Guide::url( 'getting-started/setup' ) ); ?>"><?php esc_html_e( 'Read the setup guide', 'woo-poly-integration' ); ?> &nearr;</a>
    </header>
    <hr class="wp-header-end">

    <?php if ($result) : ?>
        <div class="notice <?php echo $result['error'] ? 'notice-error' : (!empty($result['warning']) ? 'notice-warning' : 'notice-success'); ?> inline"><p><?php echo esc_html($result['message']); ?></p></div>
    <?php endif; ?>

			<section class="wpi-setup-card" id="woopoly-maintainer" aria-labelledby="wpi-maintainer-title">
				<h2 id="wpi-maintainer-title"><?php esc_html_e( 'A note from Hyyan', 'woo-poly-integration' ); ?></h2>
				<?php require dirname( __DIR__ ) . '/social.php'; ?>
			</section>

    <div class="wpi-setup-layout">
        <aside class="wpi-setup-nav" aria-label="<?php esc_attr_e('Setup progress', 'woo-poly-integration'); ?>">
            <p><strong><?php esc_html_e('Your setup', 'woo-poly-integration'); ?></strong> <span><?php echo esc_html($progress . '/3'); ?></span></p>
            <progress aria-label="<?php esc_attr_e('Setup progress', 'woo-poly-integration'); ?>" value="<?php echo esc_attr($progress); ?>" max="3"><?php echo esc_html($progress . '/3'); ?></progress>
            <ol role="list">
                <?php
                $steps = array(
                    'required-plugins' => array(__('Connect your plugins', 'woo-poly-integration'), $dependenciesReady),
                    'store-languages' => array(__('Choose your languages', 'woo-poly-integration'), $languagesReady),
                    'store-pages' => array(__('Prepare your pages', 'woo-poly-integration'), $pagesReady),
                );
                $nextStep = true;
                $stepNumber = 0;
                foreach ($steps as $target => $step) :
                    list($label, $complete) = $step;
                    $current = !$complete && $nextStep;
                    if ($current) {
                        $nextStep = false;
                    }
                    ++$stepNumber;
                    ?>
                    <li>
                        <a href="#<?php echo esc_attr($target); ?>"<?php if ($current) : ?> aria-current="step"<?php endif; ?>>
                            <span class="wpi-nav-marker<?php if ($complete) : ?> is-complete<?php endif; ?>" aria-hidden="true">
                                <?php if ($complete) : ?><span class="dashicons dashicons-yes"></span><?php else : ?><?php echo esc_html($stepNumber); ?><?php endif; ?>
                            </span>
                            <?php echo esc_html($label); ?>
                            <?php if ($complete) : ?><span class="screen-reader-text"><?php esc_html_e('Ready', 'woo-poly-integration'); ?></span><?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ol>
            <a href="#next-steps"><?php esc_html_e('Then, make it yours', 'woo-poly-integration'); ?> &darr;</a>
            <hr>
            <p class="description"><?php esc_html_e('Already have a store? Your existing pages and translations stay in place.', 'woo-poly-integration'); ?></p>
            <?php if (Plugin::canActivate()) : ?>
                <a href="<?php echo esc_url(Setup::getSetupUrl(true)); ?>"><?php esc_html_e('Advanced settings', 'woo-poly-integration'); ?> &rarr;</a>
            <?php endif; ?>
        </aside>

        <div class="wpi-setup-steps">
            <section class="wpi-setup-card" id="required-plugins" aria-labelledby="wpi-plugins-title">
                <div class="wpi-card-heading"><span class="wpi-step-number">1</span><h2 id="wpi-plugins-title"><?php esc_html_e('Connect your plugins', 'woo-poly-integration'); ?></h2><?php Setup::outputStatus($dependenciesReady); ?></div>
                <p><?php esc_html_e('WooCommerce runs your store. Polylang manages your languages. WooPoly brings them together.', 'woo-poly-integration'); ?></p>
                <div class="wpi-dependencies">
                    <?php foreach ($dependencies as $dependency) : ?>
                        <?php
                        $installed = isset($plugins[$dependency['file']]);
                        $supported = $installed && version_compare($plugins[$dependency['file']]['Version'], $dependency['minimum'], '>=');
                        $ready = $dependency['active'] && $supported;
                        ?>
                        <div class="wpi-dependency">
                            <div><h3><?php echo esc_html($dependency['name']); ?></h3>
                                <p class="description"><?php echo $ready ? esc_html__('Active and ready', 'woo-poly-integration') : ($installed ? esc_html__('Installed, needs attention', 'woo-poly-integration') : esc_html__('Not installed yet', 'woo-poly-integration')); ?></p>
                            </div>
                            <?php if ($ready) : ?>
                                <span class="dashicons dashicons-yes-alt wpi-ready-icon" aria-label="<?php esc_attr_e('Ready', 'woo-poly-integration'); ?>"></span>
                            <?php elseif ($installed && !$supported) : ?>
                                <a class="button" href="<?php echo esc_url(self_admin_url('update-core.php')); ?>"><?php esc_html_e('Update plugin', 'woo-poly-integration'); ?></a>
                            <?php elseif ($installed && current_user_can('activate_plugins')) : ?>
                                <a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(self_admin_url('plugins.php?action=activate&plugin=' . rawurlencode($dependency['file'])), 'activate-plugin_' . $dependency['file'])); ?>"><?php esc_html_e('Activate', 'woo-poly-integration'); ?></a>
                            <?php elseif (!$installed && current_user_can('install_plugins')) : ?>
                                <a class="button button-primary" href="<?php echo esc_url(add_query_arg(array('tab' => 'search', 's' => $dependency['slug']), self_admin_url('plugin-install.php'))); ?>"><?php esc_html_e('Install plugin', 'woo-poly-integration'); ?></a>
                            <?php else : ?>
                                <p><?php esc_html_e('Ask your site administrator to install and activate this plugin.', 'woo-poly-integration'); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="description"><?php esc_html_e('You can install these in any order. After a plugin opens its own setup, return to Settings → WooPoly to continue here. Polylang Pro is supported too.', 'woo-poly-integration'); ?></p>
            </section>

            <section class="wpi-setup-card" id="store-languages" aria-labelledby="wpi-languages-title">
                <div class="wpi-card-heading"><span class="wpi-step-number">2</span><h2 id="wpi-languages-title"><?php esc_html_e('Choose your languages', 'woo-poly-integration'); ?></h2><?php Setup::outputStatus($languagesReady); ?></div>
                <p><?php esc_html_e('Choose your store’s original language, then add the languages your customers use.', 'woo-poly-integration'); ?></p>
                <?php if ($languages) : ?>
                    <div class="wpi-language-list">
                        <?php foreach ($languages as $slug => $language) : ?>
                            <span><?php echo esc_html($language['name']); ?><?php if ($slug === pll_default_language()) : ?> <small><?php esc_html_e('Default', 'woo-poly-integration'); ?></small><?php endif; ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <p class="wpi-callout"><?php esc_html_e('Choose the default language carefully. Changing it after creating variable products is unsupported. Polylang’s “The language is set from content” URL mode is also unsupported.', 'woo-poly-integration'); ?></p>
                <?php if ($dependenciesReady) : ?>
                    <a class="button <?php echo $languagesReady ? '' : 'button-primary'; ?>" href="<?php echo esc_url(admin_url('admin.php?page=mlang')); ?>"><?php echo $languages ? esc_html__('Manage languages', 'woo-poly-integration') : esc_html__('Choose languages in Polylang', 'woo-poly-integration'); ?> &rarr;</a>
                    <a class="wpi-text-link" href="<?php echo esc_url(Setup::getSetupUrl()); ?>#store-languages"><?php esc_html_e('Check again', 'woo-poly-integration'); ?></a>
                <?php else : ?>
                    <p class="description"><?php esc_html_e('Activate both plugins above to choose your languages.', 'woo-poly-integration'); ?></p>
                <?php endif; ?>
            </section>

            <section class="wpi-setup-card" id="store-pages" aria-labelledby="wpi-pages-title">
                <div class="wpi-card-heading"><span class="wpi-step-number">3</span><h2 id="wpi-pages-title"><?php esc_html_e('Prepare your store pages', 'woo-poly-integration'); ?></h2><?php Setup::outputStatus($pagesReady && $languagesReady); ?></div>
                <p><?php esc_html_e('Your customers need a shop, cart, checkout, and account page in each language. WooPoly can create and link the missing pages for you.', 'woo-poly-integration'); ?></p>
                <?php if ($languagesReady) : ?>
                    <div class="wpi-page-table" tabindex="0" role="region" aria-label="<?php esc_attr_e('Store pages by language', 'woo-poly-integration'); ?>">
                        <table class="widefat">
                            <thead><tr><th scope="col"><?php esc_html_e('Page', 'woo-poly-integration'); ?></th><?php foreach ($languages as $language) : ?><th scope="col"><?php echo esc_html($language['name']); ?></th><?php endforeach; ?></tr></thead>
                            <tbody>
                                <?php foreach ($rows as $type => $row) : ?>
                                    <tr><th scope="row"><?php echo esc_html($row['label']); ?></th>
                                        <?php foreach ($row['cells'] as $slug => $cell) : ?>
                                            <td>
                                                <?php if ($cell['status'] === 'publish') : ?>
                                                    <span class="wpi-cell-ready"><?php esc_html_e('Ready', 'woo-poly-integration'); ?></span>
                                                    <a class="wpi-page-link" href="<?php echo esc_url(get_permalink($cell['id'])); ?>"><?php esc_html_e('View page', 'woo-poly-integration'); ?> &nearr;</a>
                                                <?php elseif ($cell['status'] === 'missing' || $cell['status'] === 'unassigned') : ?>
                                                    <span class="wpi-cell-pending"><?php echo $cell['status'] === 'unassigned' ? esc_html__('Needs a language', 'woo-poly-integration') : esc_html__('Missing', 'woo-poly-integration'); ?></span>
                                                    <details class="wpi-existing-page"><summary><?php esc_html_e('Use an existing page', 'woo-poly-integration'); ?></summary>
                                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                                            <input type="hidden" name="action" value="woopoly_setup_pages"><input type="hidden" name="operation" value="assign">
                                                            <input type="hidden" name="page_type" value="<?php echo esc_attr($type); ?>"><input type="hidden" name="language" value="<?php echo esc_attr($slug); ?>">
                                                            <?php wp_nonce_field('woopoly_setup_pages'); ?>
                                                            <label class="screen-reader-text" for="<?php echo esc_attr('wpi-page-' . $type . '-' . $slug); ?>"><?php echo esc_html($row['label'] . ' · ' . $languages[$slug]['name']); ?></label>
                                                            <select required id="<?php echo esc_attr('wpi-page-' . $type . '-' . $slug); ?>" name="page_id">
                                                                <option value=""><?php esc_html_e('Choose a page', 'woo-poly-integration'); ?></option>
                                                                <?php foreach ($existingPages[$slug] as $page) : ?><option value="<?php echo esc_attr($page->ID); ?>"><?php echo esc_html($page->post_title ?: '#' . $page->ID); ?></option><?php endforeach; ?>
                                                            </select>
                                                            <button class="button" type="submit"><?php esc_html_e('Use page', 'woo-poly-integration'); ?></button>
                                                        </form>
                                                    </details>
                                                <?php else : ?>
                                                    <span class="wpi-cell-pending"><?php echo esc_html(get_post_status_object($cell['status'])->label ?? $cell['status']); ?></span>
                                                    <a class="wpi-page-link" href="<?php echo esc_url(get_edit_post_link($cell['id'], 'raw')); ?>"><?php esc_html_e('Review page', 'woo-poly-integration'); ?> &rarr;</a>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!$pagesReady) : ?>
                        <form class="wpi-create-pages" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <input type="hidden" name="action" value="woopoly_setup_pages"><input type="hidden" name="operation" value="create">
                            <?php wp_nonce_field('woopoly_setup_pages'); ?>
                            <button class="button button-primary button-hero" type="submit"><?php esc_html_e('Create missing pages and translations', 'woo-poly-integration'); ?></button>
                        </form>
                    <?php else : ?>
                        <p class="wpi-success"><?php esc_html_e('Your store pages are linked and published in every configured language.', 'woo-poly-integration'); ?></p>
                    <?php endif; ?>
                    <p class="description"><?php esc_html_e('Existing pages are preserved. New translations keep the original blocks, shortcodes, and page content. Review custom text after creation; this step does not machine-translate it or create legal policies.', 'woo-poly-integration'); ?></p>
                    <p class="description"><?php esc_html_e('Page labels use installed WooCommerce translations. If labels remain in the original language, update translations in Dashboard → Updates, then edit the page titles as needed.', 'woo-poly-integration'); ?></p>
                <?php else : ?>
                    <div class="wpi-empty-state"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span><p><?php esc_html_e('Your page checklist will appear here once you have chosen a default language and at least one translation language.', 'woo-poly-integration'); ?></p></div>
                <?php endif; ?>
            </section>

            <section class="wpi-setup-card wpi-next-steps" id="next-steps" aria-labelledby="wpi-next-title">
                <p class="wpi-eyebrow"><?php esc_html_e('MAKE IT YOURS', 'woo-poly-integration'); ?></p>
                <h2 id="wpi-next-title"><?php esc_html_e('A few finishing touches', 'woo-poly-integration'); ?></h2>
                <p><?php esc_html_e('Page setup is one part of a working store. Before you welcome customers:', 'woo-poly-integration'); ?></p>
                <ul>
                    <li><a href="https://polylang.pro/documentation/support/guides/the-language-switcher/"><?php esc_html_e('Add a language switcher to your navigation.', 'woo-poly-integration'); ?></a></li>
                    <li><a href="<?php echo esc_url($dependencies[0]['active'] ? admin_url('admin.php?page=wc-admin&path=/setup-wizard') : '#required-plugins'); ?>"><?php esc_html_e('Finish WooCommerce setup: payments, shipping, and taxes.', 'woo-poly-integration'); ?></a></li>
					<li><a href="<?php echo esc_url( \Hyyan\WPI\Admin\Guide::url( 'getting-started/setup' ) . '#translate-your-first-product' ); ?>"><?php esc_html_e( 'Create your first translated product.', 'woo-poly-integration' ); ?></a></li>
                    <li><?php esc_html_e('Review your terms and privacy pages, then test a purchase and customer email in each language.', 'woo-poly-integration'); ?></li>
                </ul>
				<div class="wpi-next-footer"><span><?php esc_html_e( 'Prices and stock are shared across product translations by default.', 'woo-poly-integration' ); ?></span><a href="<?php echo esc_url( \Hyyan\WPI\Admin\Guide::url( 'settings/features' ) ); ?>"><?php esc_html_e( 'Understand synchronization', 'woo-poly-integration' ); ?> &nearr;</a></div>
            </section>
			<footer class="wpi-setup-footer"><?php esc_html_e( 'Need a hand?', 'woo-poly-integration' ); ?> <a href="<?php echo esc_url( \Hyyan\WPI\Admin\Guide::url( 'index' ) ); ?>"><?php esc_html_e( 'Browse the documentation', 'woo-poly-integration' ); ?></a> &middot; <a href="https://github.com/hyyan/woo-poly-integration/issues"><?php esc_html_e( 'Ask for help on GitHub', 'woo-poly-integration' ); ?></a></footer>
        </div>
    </div>
</div>
