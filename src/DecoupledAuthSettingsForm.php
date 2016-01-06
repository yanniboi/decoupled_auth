<?php

/**
 * @file
 * Contains \Drupal\decoupled_auth\DecoupledAuthSettingsForm
 */

namespace Drupal\decoupled_auth;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure decoupled auth settings for this site.
 */
class DecoupledAuthSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'decoupled_auth_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'decoupled_auth.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('decoupled_auth.settings');

    $form['acquisitions_registration'] = [
      '#type' => 'radios',
      '#title' => $this->t('On user registration'),
      '#default_value' => $config->get('acquisitions.registration'),
      '#options' => [
        0 => $this->t('Always create a new user'),
        1 => $this->t('Attempt to acquire an existing unauthenticated user'),
      ],
    ];

    $form['acquisitions_registration_first'] = [
      '#type' => 'radios',
      '#title' => $this->t('If there are multiple matches'),
      '#default_value' => $config->get('acquisitions.registration_first'),
      '#options' => [
        0 => $this->t('Create a new user'),
        1 => $this->t('Acquire the first match'),
      ],
      '#states' => ['visible' => ['input[name="acquisitions_registration"]' => ['value' => '1']]],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('decoupled_auth.settings')
      ->set('acquisitions.registration', $form_state->getValue('acquisitions_registration'))
      ->set('acquisitions.registration_first', $form_state->getValue('acquisitions_registration_first'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
