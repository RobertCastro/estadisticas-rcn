<?php

namespace Drupal\estadisticas_rcn\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Estadísticas RCN routes.
 */
class EstadisticasRcnController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

  public function content() {
    // Return the form.
    return \Drupal::formBuilder()->getForm('Drupal\estadisticas_rcn\Form\EstadisticasRcnFilterForm');
  }

}
