<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Admin;

/**
 * Native WooCommerce task with a link to the same setup checklist.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
final class SetupTask extends \Automattic\WooCommerce\Admin\Features\OnboardingTasks\Task
{
    /**
     * {@inheritdoc}
     */
    public function get_id()
    {
        return 'woopoly-setup';
    }

    /**
     * {@inheritdoc}
     */
    public function get_title()
    {
        return __('Set up your multilingual store', 'woo-poly-integration');
    }

    /**
     * {@inheritdoc}
     */
    public function get_content()
    {
        return __('Choose your languages and prepare your translated store pages with WooPoly.', 'woo-poly-integration');
    }

    /**
     * {@inheritdoc}
     */
    public function get_time()
    {
        return __('5 minutes', 'woo-poly-integration');
    }

    /**
     * {@inheritdoc}
     */
    public function get_action_url()
    {
        return Setup::getSetupUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function get_action_label()
    {
        return __('Open setup', 'woo-poly-integration');
    }

    /**
     * {@inheritdoc}
     */
    public function can_view()
    {
        return current_user_can('manage_options');
    }

    /**
     * {@inheritdoc}
     */
    public function is_complete()
    {
        return Setup::isReady();
    }
}
