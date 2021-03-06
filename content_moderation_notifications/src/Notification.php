<?php

namespace Drupal\content_moderation_notifications;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Mail\MailManager;

/**
 * General service for moderation-related questions about Entity API.
 */
class Notification implements NotificationInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $mail_manager;

  /**
   * Creates a new ModerationInformation instance.
   *
   * @param \Drupal\Core\Mail\MailManager $mail_manager
   *   The mail manager.
   */
  public function __construct(MailManager $mail_manager) {
    $this->mail_manager = $mail_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function sendNotification(EntityInterface $entity, $notifications) {

    foreach ($notifications as $notification) {
      $data['langcode'] = \Drupal::currentUser()->getPreferredLangcode();
      $data['notification'] = $notification;
      // Setup the email subject and body content.
      $data['params']['subject'] = $notification->subject;
      $data['params']['message'] = check_markup($notification->body['value'], $notification->body['format']);

      // Add the entity as context to aid in token replacement.
      $data['params']['context'] = array(
        'entity' => $entity,
        'user' => \Drupal::currentUser(),
        $entity->getEntityTypeId() => $entity,
      );


      // Figure out who the email should be going to.
      $data['to'] = [];

      // Authors.
      if ($notification->author) {
        $data['to'][] = $entity->getOwner()->mail->value;
      }

      // Roles.
      $roles = array_keys(array_filter($notification->roles));
      foreach ($roles as $role) {
        $role_users = \Drupal::service('entity_type.manager')
          ->getStorage('user')
          ->loadByProperties(['roles' => $role]);
        foreach ($role_users as $role_user) {
          $data['to'][] = $role_user->mail->value;
        }
      }

      // Adhoc emails.
      $adhoc_emails = array_map('trim', explode(',', $notification->emails));
      foreach ($adhoc_emails as $email) {
        $data['to'][] = $email;
      }

      // Let other modules to alter the email data.
      \Drupal::moduleHandler()->alter('content_moderation_notification_mail_data', $entity, $data);

      // Remove any null values that have crept in.
      $data['to'] = array_filter($data['to']);

      // Remove any duplicates.
      $data['to'] = array_unique($data['to']);

      $result = $this->mail_manager->mail('content_moderation_notifications', 'content_moderation_notification', implode(',', $data['to']), $data['langcode'], $data['params'], NULL, TRUE);
      if ($result['result'] !== TRUE) {
        drupal_set_message(t('There was a problem sending the notification email.'), 'error');
      }
    }
  }
}
