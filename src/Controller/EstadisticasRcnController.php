<?php

namespace Drupal\estadisticas_rcn\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

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
    return \Drupal::formBuilder()->getForm('Drupal\estadisticas_rcn\Form\EstadisticasRcnFilterForm');
  }

  /**
   * Retorna los contenidos creados entre las fechas configuradas.
   */
  public function contenidosPorFecha() {

    $build = [];
    // Incluir el formulario de fechas
    $build['form'] = \Drupal::formBuilder()->getForm('Drupal\estadisticas_rcn\Form\EstadisticasRcnFilterForm');

    $configFilter = $this->config('estadisticas_rcn.settings');
    $fecha_inicial = $configFilter->get('fecha_inicial');
    $fecha_final = $configFilter->get('fecha_final');

    // Convertir las fechas a formato UNIX timestamp.
    $fecha_inicial = strtotime($fecha_inicial);
    $fecha_final = strtotime($fecha_final) + (24 * 60 * 60 - 1); // Incluir todo el día final.

    // Obtener los tipos de contenido seleccionados desde la configuración.
    $config = $this->config('estadisticas_rcn.settings');
    $content_types = $config->get('content_types');

    // Filtrar los nodos por tipo de contenido y rango de fechas.
    $nids = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', array_keys(array_filter($content_types)), 'IN')
      ->condition('created', [$fecha_inicial, $fecha_final], 'BETWEEN')
      ->sort('created', 'DESC')
      ->execute();

    $nodes = Node::loadMultiple($nids);

    // Verifica si hay nodos
    if (empty($nodes)) {
      $build[] = [
        '#markup' => $this->t('No hay contenidos creados entre las fechas seleccionadas.'),
      ];
      return $build;
    }

    foreach ($nodes as $node) {
      // Construir el render array 
      $build[] = [
        '#markup' => $node->label() . ' (' . $node->id() . ')',
      ];
    }

    return $build;
  }

}
