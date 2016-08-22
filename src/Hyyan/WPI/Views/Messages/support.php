<?php
if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td width="20%" align="middle" style="vertical-align: central">
            <img src="http://www.gravatar.com/avatar/4f51a48cb8de5bb5c4c2bfcb9c6ef500?s=150"
                 style="padding: 2% ;vertical-align: top"/>
        </td>
        <td style="padding-left: 2% ; padding-right: 2%">
            <h3>
                <?php
                _e(
                        'Hyyan WooCommerce Polylang Integration Plugin', 'woo-poly-integration'
                )
                ?>
            </h3>
            <p><?php echo \Hyyan\WPI\Plugin::getView('badges'); ?></p>
            <p>
                <?php
                _e('Hello, my name is <b>Hyyan Abo Fakher</b>, and I am the developer
                   of plugin <b>Hyyan WooCommerce Polylang Integration</b>.<br>
                   If you like this plugin, please write a few words about it
                   at the <a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/woo-poly-integration">wordpress.org</a>
                   or <a target="_blank" href="https://twitter.com">twitter</a>
                   It will help other people
                   find this useful plugin more quickly.<br><b>Thank you!</b>', 'woo-poly-integration'
                );
                ?>
            <p>
            <hr>
            <?php echo \Hyyan\WPI\Plugin::getView('social') ?>
        </td>
    </tr>
</table>
