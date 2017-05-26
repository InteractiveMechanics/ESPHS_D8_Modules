<?php

namespace Drupal\content_moderation_notifications;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for notification service.
 */
interface NotificationInterface {

  /**
   * Send the notifications based on the entity and current transition
   * notifications.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we may be moderating.
   * @param array $notifications
   *   List of \Drupal\content_moderation_notifications\Entity\ContentModerationNotification.
   *
   * @return bool
   *   TRUE if this entity is moderated, FALSE otherwise.
   */
  public function sendNotification(EntityInterface $entity, $notifications);
}
