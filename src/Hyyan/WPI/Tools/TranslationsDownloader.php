<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Tools;

use Hyyan\WPI\HooksInterface;

/**
 * TranslationsDownloader.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class TranslationsDownloader
{
    /**
     * Download translation files from woocommerce repo.
     *
     * @global \WP_Filesystem_Base $wp_filesystem
     *
     * @param string $locale locale
     * @param string $name   language name
     *
     * @return bool true when the translation is downloaded successfully
     *
     * @throws \RuntimeException on errors
     */
    public static function download($locale, $name)
    {
        /* Check if already downloaded */
        if (static::isDownloaded($locale)) {
            return true;
        }

        /* Check if we can download */
        if (!static::isAvaliable($locale)) {
            $notAvaliable = sprintf(
                    __(
                            'Woocommerce translation %s can not be found in : <a href="%2$s">%2$s</a>', 'woo-poly-integration'
                    ), sprintf('%s(%s)', $name, $locale), static::getRepoUrl()
            );

            throw new \RuntimeException($notAvaliable);
        }

        /* Download the language pack */
        $cantDownload = sprintf(
                __('Unable to download woocommerce translation %s from : <a href="%2$s">%2$s</a>', 'woo-poly-integration'), sprintf('%s(%s)', $name, $locale), static::getRepoUrl()
        );
        $response = wp_remote_get(
                sprintf('%s/%s.zip', static::getRepoUrl(), $locale), array('sslverify' => false, 'timeout' => 200)
        );

        if (
                !is_wp_error($response) &&
                ($response['response']['code'] >= 200 &&
                $response['response']['code'] < 300)
        ) {

            /* Initialize the WP filesystem, no more using 'file-put-contents' function */
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH.'/wp-admin/includes/file.php';

                if (false === ($creds = request_filesystem_credentials('', '', false, false, null))) {
                    throw new \RuntimeException($cantDownload);
                }

                if (!WP_Filesystem($creds)) {
                    throw new \RuntimeException($cantDownload);
                }
            }

            $uploadDir = wp_upload_dir();
            $file = trailingslashit($uploadDir['path']).$locale.'.zip';

            /* Save the zip file */
            if (!$wp_filesystem->put_contents($file, $response['body'], FS_CHMOD_FILE)) {
                throw new \RuntimeException($cantDownload);
            }

            /* Unzip the file to wp-content/languages/plugins directory */
            $dir = trailingslashit(WP_LANG_DIR).'plugins/';
            $unzip = unzip_file($file, $dir);
            if (true !== $unzip) {
                throw new \RuntimeException($cantDownload);
            }

            /* Delete the package file */
            $wp_filesystem->delete($file);

            return true;
        } else {
            throw new \RuntimeException($cantDownload);
        }
    }

    /**
     * Check if the language pack is avaliable in the language repo.
     *
     * @param string $locale locale
     *
     * @return bool true if exists , false otherwise
     */
    public static function isAvaliable($locale)
    {
        $response = wp_remote_get(
                sprintf('%s/%s.zip', static::getRepoUrl(), $locale), array('sslverify' => false, 'timeout' => 200)
        );

        if (
                !is_wp_error($response) &&
                ($response['response']['code'] >= 200 &&
                $response['response']['code'] < 300)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if woocommerce language file is already downloaded.
     *
     * @param string $locale locale
     *
     * @return bool true if downloded , false otherwise
     */
    public static function isDownloaded($locale)
    {
        return file_exists(
                sprintf(
                        trailingslashit(WP_LANG_DIR)
                        .'plugins/woocommerce-%s.mo', $locale
                )
        );
    }

    /**
     * Get language repo URL.
     *
     * @return string
     */
    public static function getRepoUrl()
    {
        $url = sprintf(
                'https://downloads.wordpress.org/translation/plugin/woocommerce/%s', WC()->version
        );

        return apply_filters(HooksInterface::LANGUAGE_REPO_URL_FILTER, $url);
    }
}
