<?php

namespace Drupal\estadisticas_rcn\Controller;

use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

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
    $build['form'] = \Drupal::formBuilder()->getForm('Drupal\estadisticas_rcn\Form\EstadisticasRcnFilterForm');
    $config = $this->config('estadisticas_rcn.settings');

    $fecha_inicial = $config->get('fecha_inicial');
    $fecha_final = $config->get('fecha_final');
    $default_base_url = $config->get('base_url');

    $fecha_inicial = strtotime($fecha_inicial);
    $fecha_final = strtotime($fecha_final) + (24 * 60 * 60 - 1);
    $content_types = $config->get('content_types');


    $nids = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', array_keys(array_filter($content_types)), 'IN')
      ->condition('created', [$fecha_inicial, $fecha_final], 'BETWEEN')
      ->range(0,10)
      ->sort('created', 'DESC')
      ->execute();

    $build['btn_export'] = [
      '#type' => 'link',
      '#title' => $this->t('Exportar CSV'),
      '#url' => Url::fromRoute('estadisticas_rcn.exportcsv'),
      '#attributes' => ['class' => ['button', 'button--action', 'button--primary' ]],
    ];

    $nodes = Node::loadMultiple($nids);

    if (empty($nodes)) {
      $build[] = [
        '#markup' => $this->t('No hay contenidos creados entre las fechas seleccionadas.'),
      ];
      return $build;
    }

    $items = [];
    foreach ($nodes as $node) {

      if ($node->hasField('field_seccion') && !$node->get('field_seccion')->isEmpty()) {
          $section_entity = $node->field_seccion->entity;
          $section_label = $section_entity ? $section_entity->getName() : $this->t('No especificado');
      } else {
          $section_label = $this->t('No especificado');
      }
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

    $build['content_table'] = [
      '#theme' => 'estadisticas_rcn_contenidos',
      '#items' => $items,
      '#baseurl' => $default_base_url
    ];

    return $build;
  }

  public function ExportCSV() {
    
    $build = [];
    $config = $this->config('estadisticas_rcn.settings');
    $fecha_inicial = $config->get('fecha_inicial');
    $fecha_final = $config->get('fecha_final');
    $default_base_url = $config->get('base_url');
    $content_types = $config->get('content_types');

    $fecha_inicial = strtotime($fecha_inicial);
    $fecha_final = strtotime($fecha_final) + (24 * 60 * 60 - 1);

    $nids = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', array_keys(array_filter($content_types)), 'IN')
      ->condition('created', [$fecha_inicial, $fecha_final], 'BETWEEN')
      ->range(0,10)
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

      if ($node->hasField('field_seccion') && !$node->get('field_seccion')->isEmpty()) {
          $section_entity = $node->field_seccion->entity;
          $section_label = $section_entity ? $section_entity->getName() : $this->t('No especificado');
      } else {
          $section_label = $this->t('No especificado');
      }
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

    $csv_lines = [];
    $csv_lines[] = '"Title","Date","Author","Section","Has Video","Link","Content Type"'; // Encabezados del CSV

    foreach ($items as $item) {
        $csv_lines[] = '"' . implode('","', array_map('strval', $item)) . '"';
    }

    $csv_content = implode("\r\n", $csv_lines);

    $response = new Response($csv_content);
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

    return $response;

  }

}
