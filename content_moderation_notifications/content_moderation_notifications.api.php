<?php

/**
 * @file
 * Callbacks and hooks related to content_moderation_notifications.
 */

/**
 * Alter mail information before sending.
 *
 * Called by
 * Drupal\content_moderation_notifications\Notification::sendNotification().
 *
 * @param  $entity array
 *   The moderated entity.
 * @param  $data array
 *   The mail information.
 *
 */
function hook_content_moderation_notification_mail_data_alter($entity, &$data) {
  // Add an extra email address to the list.
  $data['to'][] = 'example@example.com';
}
