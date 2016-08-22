<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

/**
 * Messages Interface.
 *
 * Constains messages IDS used in this plugin
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
interface MessagesInterface
{
    /**
     * Support message.
     */
    const MSG_SUPPORT = 'wpi-support';

    /**
     * Activate Error Message.
     */
    const MSG_ACTIVATE_ERROR = 'wpi-activate-error';

    /**
     * Endpoints translations message.
     */
    const MSG_ENDPOINTS_TRANSLATION = 'wpi-endpoints-translations';
}
