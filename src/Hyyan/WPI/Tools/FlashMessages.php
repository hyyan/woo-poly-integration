<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Tools;

/**
 * FlashMessages.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
final class FlashMessages
{
    /**
     * Register the falsh messages.
     */
    public static function register()
    {
        add_action('admin_notices', array(
            __CLASS__, 'display',
        ));
    }

    /**
     * Queue flash message.
     *
     * @param string $id      message id
     * @param string $message message content
     * @param array  $classes array of classes to used for this message wrapper
     * @param bool   $persist should persist the message between sessions
     */
    public static function add($id, $message, array $classes = array('updated'), $persist = false)
    {
        $messages = static::getMessages();
        $data = array(
            'id' => $id,
            'message' => $message,
            'classes' => $classes,
            'persist' => $persist,
        );

        /* Add the new message data */
        if (isset($messages[$id])) {
            $messages[$id] = array_replace_recursive($messages[$id], $data);
        } else {
            $messages[$id] = $data;
        }

        update_option(static::getOptionName(), $messages);
    }

    /**
     * Remove message by its id.
     *
     * @param string $id message id
     *
     * @return bool true if removed , false otherwise
     */
    public static function remove($id)
    {
        $messages = static::getMessages();
        if (isset($messages[$id])) {
            unset($messages[$id]);
            update_option(static::getOptionName(), $messages);

            return true;
        }

        return false;
    }

    /**
     * Display all flash messages.
     */
    public static function display()
    {
        $messages = static::getMessages();

        foreach ($messages as $id => $message) {
            $display = true;
            if (!isset($message['displayed'])) {
                $display = true;
            } elseif ($message['persist'] === false && $message['displayed'] === true) {
                $display = false;
            }

            $messages[$id]['displayed'] = (true === $message['persist']) ?
                    false : true;

            if (true === $display || !@$message['displayed']) {
                $message['classes'][] = 'is-dismissible notice';
                $classesString = implode(' ', $message['classes']);
                printf(
                        '<div class="%s"><p>%s</p></div>', $classesString, $message['message']
                );
            }
        }

        update_option(static::getOptionName(), $messages);
    }

    /**
     * Clear all messages.
     */
    public static function clearMessages()
    {
        delete_option(static::getOptionName());
    }

    /**
     * Get option name used to save flash messages to database.
     *
     * @return string
     */
    public static function getOptionName()
    {
        return 'hyyan-wpi-flash-messages';
    }

    /**
     * Get messages.
     *
     * Get flash messages array
     *
     * @return array
     */
    private static function getMessages()
    {
        return get_option(static::getOptionName(), array());
    }
}
