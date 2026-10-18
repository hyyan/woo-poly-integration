<?php

if (wp_get_environment_type() !== 'local' || $GLOBALS['wpdb']->prefix !== 'wpi_manual_') {
    return;
}

// Capture mail locally instead of sending it through a mail transport.
add_filter('pre_wp_mail', static function ($result, $mail) {
    $record = array(
        'time' => gmdate('c'),
        'to' => $mail['to'],
        'subject' => $mail['subject'],
        'message' => $mail['message'],
        'locale' => get_locale(),
    );
    $written = file_put_contents(
        '/var/www/manual-mail/mail.jsonl',
        wp_json_encode($record) . "\n",
        FILE_APPEND | LOCK_EX
    );
    return $written !== false;
}, 10, 2);

// The native administration screen requires store management permission.
add_action('admin_menu', static function () {
    add_management_page(
        'Manual store mail',
        'Manual store mail',
        'manage_woocommerce',
        'wpi-manual-mail',
        static function () {
            if (!current_user_can('manage_woocommerce')) {
                wp_die('Access denied');
            }
            echo '<div class="wrap"><h1>Manual store mail</h1>';
            echo '<p>Messages are captured locally. No email is sent.</p>';
            $file = '/var/www/manual-mail/mail.jsonl';
            $lines = is_file($file) ? file($file, FILE_IGNORE_NEW_LINES) : array();
            foreach (array_reverse(array_slice($lines, -20)) as $line) {
                $mail = json_decode($line, true);
                if (!$mail) {
                    continue;
                }
                echo '<h2>' . esc_html($mail['subject']) . '</h2>';
                echo '<p>' . esc_html($mail['time'] . ' | ' . $mail['locale']) . '</p>';
                echo '<p>' . esc_html(implode(', ', (array) $mail['to'])) . '</p>';
                echo '<iframe title="Captured message" sandbox="" style="width:100%;height:450px"';
                echo ' srcdoc="' . esc_attr(wp_kses_post($mail['message'])) . '"></iframe>';
            }
            echo '</div>';
        }
    );
});
