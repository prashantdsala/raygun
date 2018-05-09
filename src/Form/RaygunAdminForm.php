<?php

/**
 * @file
 * Contains \Drupal\raygun\Form\RaygunAdminForm.
 */

namespace Drupal\raygun\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class RaygunAdminForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'raygun_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['raygun.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['common'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Common'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $form['common']['raygun_apikey'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('API key'),
      '#description' => $this->t('Raygun.io API key for the application.'),
      '#default_value' => $this->config('raygun.settings')->get('raygun_apikey'),
    ];
    $form['common']['raygun_async_sending'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use asynchronous sending'),
      '#description' => $this->t('If checked, the message will be sent asynchronously. This provides a great speedup versus the older cURL method. On some environments (e.g. Windows), you might be forced to uncheck this.'),
      '#default_value' => $this->config('raygun.settings')->get('raygun_async_sending'),
    ];
    $form['common']['raygun_send_version'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Send application version'),
      '#description' => $this->t('If checked, all error messages to Raygun.io will include your application version that is currently running. This is optional but recommended as the version number is considered to be first-class data for a message.'),
      '#default_value' => $this->config('raygun.settings')->get('raygun_send_version'),
    ];
    $form['common']['raygun_application_version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Application version'),
      '#description' => $this->t('What is the current version of your Drupal application. This can be any string or number or even a git commit hash.'),
      '#default_value' => $this->config('raygun.settings')->get('raygun_application_version'),
      '#states' => [
        'invisible' => [
          ':input[name="raygun_send_version"]' => [
            'checked' => FALSE
            ]
          ]
        ],
    ];
    $form['common']['raygun_send_email'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Send current user email'),
      '#description' => $this->t('If checked, all error messages to Raygun.io will include the current email address of any logged in users.  This is optional - if it is not checked, a random identifier will be used.'),
      '#default_value' => $this->config('raygun.settings')->get('raygun_send_email'),
    ];


    $form['php'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('PHP'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
    $form['php']['raygun_exceptions'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Register global exception handler'),
      '#default_value' => $this->config('raygun.settings')->get('raygun_exceptions'),
    ];
    $form['php']['raygun_error_handling'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Register global error handler'),
      '#default_value' => $this->config('raygun.settings')->get('raygun_error_handling'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Simple API key pattern matching.
    // @TODO confirm that this is the correct pattern for all accounts.
    if (!preg_match("/^[0-9a-zA-Z\+\/]{22}==$/", $values['raygun_apikey'])) {
      $form_state->setErrorByName('raygun_apikey', $this->t('You must specify a valid Raygun.io API key. You can find this on your dashboard.'));
    }

    $application_version = trim($values['raygun_application_version']);
    if ($values['raygun_send_version'] && empty($application_version)) {
      $form_state->setErrorByName('raygun_application_version', $this->t('You must specify an application version if you are going to send this.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('raygun.settings');

    $config->set('raygun_apikey', $form_state->getValue('raygun_apikey'));
    $config->set('raygun_async_sending', $form_state->getValue('raygun_async_sending'));
    $config->set('raygun_send_version', $form_state->getValue('raygun_send_version'));
    $config->set('raygun_application_version', $form_state->getValue('raygun_application_version'));
    $config->set('raygun_send_email', $form_state->getValue('raygun_send_email'));
    $config->set('raygun_exceptions', $form_state->getValue('raygun_exceptions'));
    $config->set('raygun_error_handling', $form_state->getValue('raygun_error_handling'));
    $config->set('raygun_send_username', $form_state->getValue('raygun_send_username'));

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
?>
