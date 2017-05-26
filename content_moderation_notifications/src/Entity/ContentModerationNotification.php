<?php

namespace Drupal\content_moderation_notifications\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the content_moderation_notification entity.
 *
 * The lines below, starting with '@ConfigEntityType,' are a plugin annotation.
 * These define the entity type to the entity type manager.
 *
 * The properties in the annotation are as follows:
 *  - id: The machine name of the entity type.
 *  - label: The human-readable label of the entity type. We pass this through
 *    the "@Translation" wrapper so that the multilingual system may
 *    translate it in the user interface.
 *  - handlers: An array of entity handler classes, keyed by handler type.
 *    - access: The class that is used for access checks.
 *    - list_builder: The class that provides listings of the entity.
 *    - form: An array of entity form classes keyed by their operation.
 *  - entity_keys: Specifies the class properties in which unique keys are
 *    stored for this entity type. Unique keys are properties which you know
 *    will be unique, and which the entity manager can use as unique in database
 *    queries.
 *  - links: entity URL definitions. These are mostly used for Field UI.
 *    Arbitrary keys can set here. For example, User sets cancel-form, while
 *    Node uses delete-form.
 *
 * @see http://previousnext.com.au/blog/understanding-drupal-8s-config-entities
 * @see annotation
 * @see Drupal\Core\Annotation\Translation
 *
 * @ingroup content_moderation_notifications
 *
 * @ConfigEntityType(
 *   id = "content_moderation_notification",
 *   label = @Translation("Notification"),
 *   admin_permission = "administer content moderation notifications",
 *   handlers = {
 *     "access" = "Drupal\content_moderation_notifications\ContentModerationNotificationsAccessController",
 *     "list_builder" = "Drupal\content_moderation_notifications\Controller\ContentModerationNotificationsListBuilder",
 *     "form" = {
 *       "add" = "Drupal\content_moderation_notifications\Form\ContentModerationNotificationsAddForm",
 *       "edit" = "Drupal\content_moderation_notifications\Form\ContentModerationNotificationsEditForm",
 *       "delete" = "Drupal\content_moderation_notifications\Form\ContentModerationNotificationsDeleteForm"
 *     }
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "/examples/content_moderation_notifications/manage/{content_moderation_notification}",
 *     "delete-form" = "/examples/content_moderation_notifications/manage/{content_moderation_notification}/delete"
 *   },
 *   config_export = {
 *     "id",
 *     "workflow",
 *     "recipients",
 *     "transitions",
 *     "roles",
 *     "author",
 *     "emails",
 *     "subject",
 *     "body",
 *     "label",
 *   }
 * )
 */
class ContentModerationNotification extends ConfigEntityBase {

  /**
   * The content_moderation_notification ID.
   *
   * @var string
   */
  public $id;

  /**
   * The content_moderation_notification UUID.
   *
   * @var string
   */
  public $uuid;
  public $workflow;
  public $roles;
  public $transition;

  /**
   * The content_moderation_notification floopy flag.
   *
   * @var string
   */
  public $floopy;

}
