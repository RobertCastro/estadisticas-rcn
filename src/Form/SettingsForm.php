<?php

namespace Drupal\estadisticas_rcn\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;

/**
 * Configure Estadísticas RCN settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'estadisticas_rcn_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['estadisticas_rcn.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get the content types.
    $content_types = NodeType::loadMultiple();
    // Prepare options array.
    $options = [];
    foreach ($content_types as $content_type) {
      $options[$content_type->id()] = $content_type->label();
    }

    // Create checkboxes form element with content types.
    $form['content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content Types'),
      '#options' => $options,
      '#default_value' => $this->config('estadisticas_rcn.settings')->get('content_types') ?: [],
      '#description' => $this->t('Select the content types to include in statistics.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validation is not necessary for checkboxes if you're only interested in
    // the checked values, but you could add validation if needed.
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the selected content types.
    $this->config('estadisticas_rcn.settings')
      ->set('content_types', array_filter($form_state->getValue('content_types')))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
