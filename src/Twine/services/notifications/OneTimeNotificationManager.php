<?php

namespace Twine\services\notifications;

use Twine\entities\notifications\OneTimeNotification;
use WP_User;

/**
 * Class OneTimeNotificationManager
 * Manages displaying messages to be shown once.
 * @package Twine\services\notifications
 */
class OneTimeNotificationManager
{
    const META_KEY = '_twine_ot_notifications';
    /**
     * @var OneTimeNotification[]
     */
    protected $cached_notifications;

    /**
     * Gets all the one-time notifications for this user
     * @param WP_User|int $wp_user
     * @return OneTimeNotification[]
     */
    public function getOneTimeNotificationsFor($wp_user)
    {
        if ($wp_user instanceof WP_User) {
            $wp_user = $wp_user->ID;
        }
        $notification_metas = get_user_meta($wp_user, self::META_KEY, false);
        $notifications = [];
        if ($notification_metas) {
            foreach ($notification_metas as $notice_data) {
                $notifications[] = new OneTimeNotification($notice_data);
            }
        }
        return $notifications;
    }

    /**
     * Shows the one-time notifications for the current user and clears them.
     */
    public function showOneTimeNotifications()
    {
        global $current_user;
        $notifications = $this->getOneTimeNotificationsFor($current_user);
        if (doing_action('admin_notices') || did_action('admin_notices')) {
            $this->displayNotifications($notifications);
        } else {
            add_action('admin_notices', [$this, 'displayNotificationsLater']);
            $this->cached_notifications = $notifications;
        }
        $this->clearNotificationsFor($current_user);
    }

    /**
     * @param OneTimeNotification[] $notifications
     */
    protected function displayNotifications($notifications)
    {
        foreach ($notifications as $notification) {
            if ($notification instanceof OneTimeNotification) {
                // ouput the notification's HTML (which must have been sanitized upstream)
                //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $notification->display();
            }
        }
    }

    /**
     * Echoes out HTML for notifications. Used as a callback for 'admin_notices' action.
     */
    public function displayNotificationsLater()
    {
        $this->displayNotifications($this->cached_notifications);
    }

    /**
     * Removes all one-time notifications for the given user
     * @param WP_User|int $wp_user
     */
    public function clearNotificationsFor($wp_user)
    {
        if ($wp_user instanceof WP_user) {
            $wp_user = $wp_user->ID;
        }
        delete_user_meta($wp_user, self::META_KEY);
    }

    /**
     * Adds a one-time notification for any user.
     * @param WP_User $wp_user
     * @param string $type constants on \Twine\entities\notifications\OneTimeNotification
     * @param string $html
     */
    public function addHtmlNotification($wp_user, $type, $html)
    {
        if ($wp_user instanceof WP_User) {
            $wp_user = $wp_user->ID;
        }
        add_user_meta(
            $wp_user,
            self::META_KEY,
            [
                'type' => $type,
                'html' => $html,
            ]
        );
    }

    /**
     * Adds a one-time notification for the curren tuser
     * @param string $type constants on \Twine\entities\notifications\OneTimeNotification
     * @param string $html
     */
    public function addHtmlNotificationForCurrentUser($type, $html)
    {
        global $current_user;
        $this->addHtmlNotification($current_user, $type, $html);
    }

    /**
     * @param string $type constants on \Twine\entities\notifications\OneTimeNotification
     * @param string $html html to display
     */
    public function addTextNotificationForCurrentUser($type, $html)
    {
        $this->addHtmlNotificationForCurrentUser(
            $type,
            '<p>' . $html . '</p>'
        );
    }
}
