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
    // Obtener los tipos de contenido seleccionados desde la configuración.
    $config = $this->config('estadisticas_rcn.settings');

    // $configFilter = $this->config('estadisticas_rcn.settings');
    $fecha_inicial = $config->get('fecha_inicial');
    $fecha_final = $config->get('fecha_final');
    $default_base_url = $config->get('base_url');

    // Convertir las fechas a formato UNIX timestamp.
    $fecha_inicial = strtotime($fecha_inicial);
    $fecha_final = strtotime($fecha_final) + (24 * 60 * 60 - 1); // Incluir todo el día final.

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

    $items = [];
    foreach ($nodes as $node) {

      $section_entity = $node->field_section->entity;
      $section_label = $section_entity ? $section_entity->name() : $this->t('No especificado');
      $has_video = isset($node->field_nota_con_video) ? $node->field_nota_con_video->value : FALSE;

      $items[] = [
        'title' => $node->label(),
        'date' => $node->getCreatedTime(),
        'author' => $node->getOwner()->getDisplayName(),
        'section' => $section_label,
        'has_video' => $has_video,
        'link' => $node->toUrl()->toString(),
        'content_type' => $node->bundle(),
      ];
    }

    // Agregar el array de items al build y especificar el template.
    $build['content_table'] = [
      '#theme' => 'estadisticas_rcn_contenidos',
      '#items' => $items,
      '#baseurl' => $default_base_url
    ];

    return $build;
  }

}
