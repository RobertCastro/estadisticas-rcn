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

    $config = $this->config('estadisticas_rcn.settings');

    $form['fecha_inicial'] = [
      '#type' => 'date',
      '#title' => $this->t('Fecha Inicial'),
      '#default_value' => $config->get('fecha_inicial'),
      '#required' => TRUE,
    ];

    $form['fecha_final'] = [
      '#type' => 'date',
      '#title' => $this->t('Fecha Final'),
      '#default_value' => $config->get('fecha_final'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('APLICAR FILTRO'),
    ];

    return $form;
    // return parent::buildForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Obtener la configuración editable.
    $config = \Drupal::configFactory()->getEditable('estadisticas_rcn.settings');

    // Guardar los valores en la configuración.
    $config->set('fecha_inicial', $form_state->getValue('fecha_inicial'))
           ->set('fecha_final', $form_state->getValue('fecha_final'))
           ->save();

    // Mensaje para el usuario.
    \Drupal::messenger()->addMessage($this->t('El filtro ha sido aplicado con las fechas: @fecha_inicial a @fecha_final', [
      '@fecha_inicial' => $form_state->getValue('fecha_inicial'), 
      '@fecha_final' => $form_state->getValue('fecha_final')
    ]));

    // Redirección.
    // $form_state->setRedirect('<ruta_de_destino>');
  }
  

}
