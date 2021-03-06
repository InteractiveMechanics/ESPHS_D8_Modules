<?php

namespace Drupal\content_moderation_notifications\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContentModerationNotificationFormBase.
 *
 * Typically, we need to build the same form for both adding a new entity,
 * and editing an existing entity. Instead of duplicating our form code,
 * we create a base class. Drupal never routes to this class directly,
 * but instead through the child classes of ContentModerationNotificationAddForm
 * and ContentModerationNotificationEditForm.
 *
 * @package Drupal\content_moderation_notifications\Form
 *
 * @ingroup content_moderation_notifications
 */
class ContentModerationNotificationsFormBase extends EntityForm {

  /**
   * Entity Query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQueryFactory;

  /**
   * Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Construct the ContentModerationNotificationFormBase.
   *
   * For simple entity forms, there's no need for a constructor. Our form
   * base, however, requires an entity query factory to be injected into it
   * from the container. We later use this query factory to build an entity
   * query for the exists() method.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   An entity query factory for the entity type.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An entity type manager for the entity type.
   */
  public function __construct(QueryFactory $query_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityQueryFactory = $query_factory;
  }

  /**
   * Factory method for ContentModerationNotificationFormBase.
   *
   * When Drupal builds this class it does not call the constructor directly.
   * Instead, it relies on this method to build the new object. Why? The class
   * constructor may take multiple arguments that are unknown to Drupal. The
   * create() method always takes one parameter -- the container. The purpose
   * of the create() method is twofold: It provides a standard way for Drupal
   * to construct the object, meanwhile it provides you a place to get needed
   * constructor parameters from the container.
   *
   * In this case, we ask the container for an entity query factory. We then
   * pass the factory to our class as a constructor parameter.
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.query'), $container->get('entity_type.manager'));
  }

  /**
   * Update options.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Formstate.
   *
   * @return mixed
   *   Returns the updated options.
   */
  public static function updateWorkflowTransitions(array $form, FormStateInterface &$form_state) {
    return $form['transitions_wrapper'];
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   *
   * Builds the entity add/edit form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An associative array containing the content_moderation_notification
   *   add/edit form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get anything we need from the base class.
    $form = parent::buildForm($form, $form_state);

    // Drupal provides the entity to us as a class variable. If this is an
    // existing entity, it will be populated with existing values as class
    // variables. If this is a new entity, it will be a new object with the
    // class of our entity. Drupal knows which class to call from the
    // annotation on our ContentModerationNotification class.
    $content_moderation_notification = $this->entity;

    // Retrieve a list of all possible workflows.
    $workflows = $this->entityTypeManager->getStorage('workflow')->loadMultiple();

    // Build the options array of workflows.
    $workflow_options = [];
    foreach ($workflows as $workflow_id => $workflow) {
      $workflow_options[$workflow_id] = $workflow->label();
    }

    // Default to the first workflow in the list.
    $workflow_keys = array_keys($workflow_options);

    if ($form_state->getValue('workflow')) {
      $selected_workflow = $form_state->getValue('workflow');
    }
    elseif (isset($content_moderation_notification->workflow)) {
      $selected_workflow = $content_moderation_notification->workflow;
    }
    else {
      $selected_workflow = array_shift($workflow_keys);
    }

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $content_moderation_notification->id(),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'error' => 'The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores. Additionally, it can not be the reserved word "custom".',
      ],
      '#disabled' => !$content_moderation_notification->isNew(),
    ];

    // Allow the workflow to be selected, this will dynamically update the
    // available transition lists.
    $form['workflow'] = [
      '#type' => 'select',
      '#title' => $this->t('Workflow'),
      '#options' => $workflow_options,
      '#default_value' => $selected_workflow,
      '#required' => TRUE,
      '#description' => t('Select a workflow'),
      '#ajax' => [
        'wrapper' => 'workflow_transitions_wrapper',
        'callback' => 'Drupal\content_moderation_notifications\Form\ContentModerationNotificationsFormBase::updateWorkflowTransitions',
      ],
    ];

    // Ajax replaceable fieldset.
    $form['transitions_wrapper'] = [
      '#type' => 'container',
      '#prefix' => '<div id="workflow_transitions_wrapper">',
      '#suffix' => '</div>',
    ];

    // Transitions.
    $state_transitions_options = [];
    $state_transitions = $workflows[$selected_workflow]->getTransitions();
    foreach ($state_transitions as $key => $transition) {
      $state_transitions_options[$key] = $transition->label();
    }

    $form['transitions_wrapper']['transitions'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Transitions'),
      '#options' => $state_transitions_options,
      '#default_value' => isset($content_moderation_notification->transitions) ? $content_moderation_notification->transitions : [],
      '#required' => TRUE,
      '#description' => t('Select which transitions triggers this notification.'),
    ];

    // Role selection.
    $roles_options = [];
    foreach (user_roles(TRUE) as $name => $role) {
      $roles_options[$name] = $role->label();
    }

    $form['roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Roles'),
      '#options' => $roles_options,
      '#default_value' => isset($content_moderation_notification->roles) ? $content_moderation_notification->roles : [] ,
      '#description' => t('Send notifications to all users with these roles.'),
    ];

    // Send email to author?
    $form['author'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Email the author?'),
      '#default_value' => isset($content_moderation_notification->author) ? $content_moderation_notification->author : 0,
      '#description' => t('Send notifications to the current author of the content.'),
    ];

    $form['emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Adhoc email addresses'),
      '#default_value' => isset($content_moderation_notification->emails) ? $content_moderation_notification->emails : '',
      '#description' => t('Send notifications to these email addresses, emails should be entered as a comma separated list.'),
    ];

    // Email subject line.
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => t('Email Subject'),
      '#default_value' => isset($content_moderation_notification->subject) ? $content_moderation_notification->subject : '',
      '#empty_value' => '',
    ];

    // Email body content.
    $form['body'] = [
      '#type' => 'text_format',
      '#format' => isset($content_moderation_notification->body) ? $content_moderation_notification->body['format'] : 'basic_html',
      '#title' => t('Email Body'),
      '#default_value' => isset($content_moderation_notification->body) ? $content_moderation_notification->body['value'] : '',
      '#empty_value' => '',
    ];

    // Return the form.
    return $form;
  }

  /**
   * Checks for an existing content_moderation_notification.
   *
   * @param string|int $entity_id
   *   The entity ID.
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return bool
   *   TRUE if this format already exists, FALSE otherwise.
   */
  public function exists($entity_id, array $element, FormStateInterface $form_state) {
    // Use the query factory to build a new entity query.
    $query = $this->entityQueryFactory->get('content_moderation_notification');

    // Query the entity ID to see if its in use.
    $result = $query->condition('id', $element['#field_prefix'] . $entity_id)
      ->execute();

    // We don't need to return the ID, only if it exists or not.
    return (bool) $result;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::actions().
   *
   * To set the submit button text, we need to override actions().
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An array of supported actions for the current entity form.
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // Get the basic actins from the base class.
    $actions = parent::actions($form, $form_state);

    // Change the submit button text.
    $actions['submit']['#value'] = $this->t('Save');

    // Return the result.
    return $actions;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   *
   * Saves the entity. This is called after submit() has built the entity from
   * the form values. Do not override submit() as save() is the preferred
   * method for entity form controllers.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function save(array $form, FormStateInterface $form_state) {
    // EntityForm provides us with the entity we're working on.
    $content_moderation_notification = $this->getEntity();

    // Drupal already populated the form values in the entity object. Each
    // form field was saved as a public variable in the entity class. PHP
    // allows Drupal to do this even if the method is not defined ahead of
    // time.
    $status = $content_moderation_notification->save();

    // Grab the URL of the new entity. We'll use it in the message.
    $url = $content_moderation_notification->urlInfo();

    // Create an edit link.
    $edit_link = Link::fromTextAndUrl($this->t('Edit'), $url)->toString();

    if ($status == SAVED_UPDATED) {
      // If we edited an existing entity...
      drupal_set_message($this->t('Notification has been updated.', ['%label' => $content_moderation_notification->label()]));
      $this->logger('contact')->notice('Notification has been updated.', ['%label' => $content_moderation_notification->label(), 'link' => $edit_link]);
    }
    else {
      // If we created a new entity...
      drupal_set_message($this->t('Notification has been added.', ['%label' => $content_moderation_notification->label()]));
      $this->logger('contact')->notice('Notification has been added.', ['%label' => $content_moderation_notification->label(), 'link' => $edit_link]);
    }

    // Redirect the user back to the listing route after the save operation.
    $form_state->setRedirect('entity.content_moderation_notification.list');
  }

}
