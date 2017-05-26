<?php

namespace Drupal\content_moderation_notifications;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for notification_information service.
 */
interface NotificationInformationInterface {

  /**
   * Determines if an entity is moderated.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we may be moderating.
   *
   * @return bool
   *   TRUE if this entity is moderated, FALSE otherwise.
   */
  public function isModeratedEntity(EntityInterface $entity);

  /**
   * Checks for the workflow object of the moderated entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we may be moderating.
   *
   * @return mixed
   *   The workflow object if the entity is moderated,
   *   FALSE otherwise.
   */
  public function getWorkflow(EntityInterface $entity);

  /**
   * Checks for the current transition of the moderated entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we may be moderating.
   *
   * @return bool
   *   The transition object if the entity is moderated,
   *   FALSE otherwise.
   */
  public function getTransition(EntityInterface $entity);

  /**
   * Gets the list of notification based on the current transition.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we may be moderating.
   *
   * @return array
   *   An array containing the entity and the notifications list.
   */
  public function getNotifications(EntityInterface $entity);

  /**
   * Loads the latest revision of a specific entity.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param int $entity_id
   *   The entity ID.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The latest entity revision or NULL, if the entity type / entity doesn't
   *   exist.
   */
  public function getLatestRevision($entity_type_id, $entity_id);
}
