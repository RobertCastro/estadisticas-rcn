<?php

namespace Drupal\estadisticas_rcn\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\node\Entity\NodeType;
use Drupal\Core\Url;

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

    $form['#attached']['library'][] = 'estadisticas_rcn/estadisticas_rcn';

    $config = $this->config('estadisticas_rcn.settings');

    $max_date = date('Y-m-d');
    $min_date = date('Y-m-d', strtotime('-3 months'));

    $form['fecha_inicial'] = [
        '#type' => 'date',
        '#title' => $this->t('Fecha Inicial'),
        '#default_value' => $config->get('fecha_inicial'),
        '#required' => TRUE,
        '#min' => $min_date,
        '#max' => $max_date,
    ];

    $form['fecha_final'] = [
      '#type' => 'date',
      '#title' => $this->t('Fecha Final'),
      '#default_value' => $config->get('fecha_final'),
      '#required' => TRUE,
      '#min' => $min_date,
      '#max' => $max_date,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('APLICAR FILTRO'),
    ];

    $form['btn_export'] = [
      '#type' => 'link',
      '#title' => $this->t('Exportar CSV'),
      '#url' => Url::fromRoute('estadisticas_rcn.exportcsv'),
      '#attributes' => ['class' => ['button', 'button--action', 'button--primary' ]],
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $fecha_inicial = $form_state->getValue('fecha_inicial');
    $fecha_final = $form_state->getValue('fecha_final');

    $fecha_inicial_ts = strtotime($fecha_inicial);
    $fecha_final_ts = strtotime($fecha_final);
    $min_date_ts = strtotime('-3 months -1 day');
    $max_date_ts = strtotime('now');

    if ($fecha_inicial_ts < $min_date_ts || $fecha_inicial_ts > $max_date_ts) {
        $form_state->setErrorByName('fecha_inicial', $this->t('La fecha inicial debe estar dentro de los últimos 3 meses.'));
    }
    if ($fecha_final_ts < $min_date_ts || $fecha_final_ts > $max_date_ts) {
      $form_state->setErrorByName('fecha_final', $this->t('Fecha final incorrecta.'));
  }
}


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = \Drupal::configFactory()->getEditable('estadisticas_rcn.settings');

    $config->set('fecha_inicial', $form_state->getValue('fecha_inicial'))
           ->set('fecha_final', $form_state->getValue('fecha_final'))
           ->save();

    \Drupal::messenger()->addMessage($this->t('El filtro ha sido aplicado con las fechas: @fecha_inicial a @fecha_final', [
      '@fecha_inicial' => $form_state->getValue('fecha_inicial'), 
      '@fecha_final' => $form_state->getValue('fecha_final')
    ]));

    // Redirección.
    // $form_state->setRedirect('<ruta_de_destino>');
  }
  

}
