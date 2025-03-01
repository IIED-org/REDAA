<?php

namespace Drupal\session_limit\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\session_limit\Services\SessionLimit;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Session limit configuration form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler instance to use.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'session_limit_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['session_limit.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('session_limit.settings');

    $form['session_limit_max'] = [
      '#type' => 'number',
      '#title' => $this->t('Default maximum number of active sessions'),
      '#default_value' => $config->get('session_limit_max'),
      '#min' => 0,
      '#description' => $this->t('The maximum number of active sessions a user can have. 0 implies unlimited sessions.'),
    ];

    $form['session_limit_behaviour'] = [
      '#type' => 'radios',
      '#title' => $this->t('When the session limit is exceeded'),
      '#default_value' => $config->get('session_limit_behaviour'),
      '#options' => SessionLimit::getActions(),
    ];

    if ($this->moduleHandler->moduleExists('masquerade')) {
      $form['session_limit_masquerade_ignore'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Ignore masqueraded sessions.'),
        '#description' => $this->t("When a user administrator uses the masquerade module to impersonate a different user, it won't count against the session limit counter"),
        '#default_value' => $config->get('session_limit_masquerade_ignore'),
      ];
    }

    $form['session_limit_logged_out_display_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Logged out display message'),
      '#default_value' => $config->get('session_limit_logged_out_display_message'),
      '#description' => $this->t('This is the message that is displayed to a user whose session was automatically ended due to Session Limit.'),
    ];

    $form['session_limit_logged_out_message_severity'] = [
      '#type' => 'select',
      '#title' => $this->t('Logged out message severity'),
      '#default_value' => $config->get('session_limit_logged_out_message_severity'),
      '#options' => [
        'error' => $this->t('Error'),
        'warning' => $this->t('Warning'),
        'status' => $this->t('Status'),
        '_none' => $this->t('No Message'),
      ],
      '#description' => $this->t('The severity of the message the user receives when they are logged out by session limit.'),
    ];

    $role_limits = $config->get('session_limit_roles');

    $form['session_limit_roles'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Role limits'),
      '#description' => $this->t('Optionally, specify session limits by role.'),
    ];

    $roles = array_filter(Role::loadMultiple(), fn(RoleInterface $role) => ($role->get('id') != 'anonymous'));
    foreach ($roles as $rid => $role) {
      $form['session_limit_roles'][$rid] = [
        '#type' => 'select',
        '#options' => [
          0 => $this->t('Uses default'),
          SessionLimit::USER_UNLIMITED_SESSIONS => $this->t('No limits'),
          1 => 1,
          2 => 2,
          3 => 3,
          4 => 4,
          5 => 5,
        ],
        '#title' => $role->label(),
        '#default_value' => empty($role_limits[$rid]) ? 0 : $role_limits[$rid],
      ];
    }

    $form['session_limit_admin_inclusion'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Apply session limit for User 1'),
      '#description' => $this->t("By default session limit does not check the User 1 account. Enable this checkbox for User 1 account to be included for Session Limit."),
      '#default_value' => $config->get('session_limit_admin_inclusion'),
    ];

    $form['session_limit_log_events'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Log events'),
      '#description' => $this->t('Log session limit events to Watchdog'),
      '#default_value' => $config->get('session_limit_log_events'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $maxSessions = $form_state->getValue(['session_limit_max']);
    if ($maxSessions < 0) {
      $form_state->setErrorByName('session_limit_max', $this->t('Maximum number of active sessions must be positive'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('session_limit.settings');

    $config->set('session_limit_max', $form_state->getValue($form['session_limit_max']['#parents']));
    $config->set('session_limit_behaviour', $form_state->getValue($form['session_limit_behaviour']['#parents']));
    $config->set('session_limit_logged_out_display_message', $form_state->getValue($form['session_limit_logged_out_display_message']['#parents']));
    $config->set('session_limit_logged_out_message_severity', $form_state->getValue($form['session_limit_logged_out_message_severity']['#parents']));
    $config->set('session_limit_admin_inclusion', $form_state->getValue($form['session_limit_admin_inclusion']['#parents']));
    $config->set('session_limit_log_events', $form_state->getValue($form['session_limit_log_events']['#parents']));

    $role_limits = [];
    $roles = array_filter(Role::loadMultiple(), fn(RoleInterface $role) => ($role->get('id') != 'anonymous'));
    foreach ($roles as $rid => $role) {
      $role_limits[$rid] = $form_state->getValue($form['session_limit_roles'][$rid]['#parents']);
    }

    $config->set('session_limit_roles', $role_limits);

    if (!empty($form['session_limit_masquerade_ignore'])) {
      $config->set('session_limit_masquerade_ignore', $form_state->getValue($form['session_limit_masquerade_ignore']['#parents']));
    }

    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

}
