<?php

namespace Drupal\estadisticas_rcn\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\node\Entity\NodeType;

class EstadisticasRcnFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'estadisticas_rcn_filter_form';
  }

  /**
   * Build the form.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['fecha_inicial'] = [
      '#type' => 'date',
      '#title' => $this->t('Fecha Inicial'),
      '#required' => TRUE,
    ];

    $form['fecha_final'] = [
      '#type' => 'date',
      '#title' => $this->t('Fecha Final'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    ];

    // return $form;
    return parent::buildForm($form, $form_state);
  }

  /**
   * Form submission handler.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Handle the form submission, e.g., set a redirect or perform a query.
    parent::submitForm($form, $form_state);
  }

}
