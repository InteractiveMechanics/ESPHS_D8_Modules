<?php

namespace Drupal\content_moderation_notifications;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\content_moderation\ModerationInformationInterface;

/**
 * General service for notification related questions about the moderated
 * entity.
 */
class NotificationInformation implements NotificationInformationInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * General service for moderation-related questions about Entity API.
   *
   * @var \Drupal\content_moderation\ModerationInformation
   */
  protected $moderationInformation;

  /**
   * Creates a new NotificationInformation instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderation_information
   *   The bundle information service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModerationInformationInterface $moderation_information) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moderationInformation = $moderation_information;
  }

  /**
   * {@inheritdoc}
   */
  public function isModeratedEntity(EntityInterface $entity) {
    return $this->moderationInformation->isModeratedEntity($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkflow(EntityInterface $entity) {
    return $this->isModeratedEntity($entity) ? $this->moderationInformation
      ->getWorkflowForEntity($entity) : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTransition(EntityInterface $entity) {
    $transition = FALSE;
    if (($workflow = $this->getWorkflow($entity))) {
      $current_state = $entity->moderation_state->value;
      $previous_state = isset($entity->last_revision)? $entity->last_revision->moderation_state->value : $workflow->getInitialState()->id();
      $transition = $workflow->getTransitionFromStateToState($previous_state, $current_state);
    }

    return $transition;
  }

  /**
   * {@inheritdoc}
   */
  public function getNotifications(EntityInterface $entity) {
    $notifications = [
      'entity' => $entity,
      'notifications' => [],
    ];

    if ($this->isModeratedEntity($entity)) {
      $workflow = $this->getWorkflow($entity);
      $transition = $this->getTransition($entity);
      // Find out if we have a config entity that contains this transition.
      $query = \Drupal::entityQuery('content_moderation_notification')
        ->condition('workflow', $workflow->id())
        ->condition('transitions.' . $transition->id(), $transition->id());

      $notification_ids = $query->execute();

      $notifications['notifications'] = $this->entityTypeManager
        ->getStorage('content_moderation_notification')
        ->loadMultiple($notification_ids);
    }

    return $notifications;
  }

  /**
   * {@inheritdoc}
   */
  function getLatestRevision($entity_type_id, $entity_id) {
    return $this->moderationInformation->getLatestRevision($entity_type_id, $entity_id);
  }
}
