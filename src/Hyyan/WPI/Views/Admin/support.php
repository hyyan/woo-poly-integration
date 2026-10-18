<?php
if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>
<h3><span><?php esc_html_e('Keep in touch', 'woo-poly-integration'); ?></span></h3>
<div class="inside">
    <p><?php esc_html_e('If WooPoly is useful to you, a GitHub star helps others discover it. You are also welcome to follow Hyyan Abo Fakher on GitHub or LinkedIn for more open-source work.', 'woo-poly-integration'); ?></p>
    <p><?php esc_html_e('Bug reports, translations, and contributions are always welcome.', 'woo-poly-integration'); ?></p>
    <?php include dirname(__DIR__).'/social.php'; ?>
</div>
