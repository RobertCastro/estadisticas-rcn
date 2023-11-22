<?php

namespace Drupal\estadisticas_rcn\Controller;

use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\node\NodeInterface;
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
    // $default_base_url = $config->get('base_url');

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
      $has_video = $node->hasField('field_nota_con_video') && !$node->get('field_nota_con_video')->isEmpty() ? 'Si' : 'No';

      $produccion = str_replace('_', '-', $node->field_domain_access->target_id);
      $domainStorage = \Drupal::entityTypeManager()->getStorage('domain')->load($node->field_domain_access->target_id);
      $nombre_produccion = $domainStorage->get('name');

      $content_type = $node->bundle();
      $content_type_entity = \Drupal::entityTypeManager()->getStorage('node_type')->load($content_type);
      $content_type_name = $content_type_entity ? $content_type_entity->label() : '';

      $items[] = [
        'title' => $node->label(),
        'date' => $node->getCreatedTime(),
        'author' => $node->getOwner()->getDisplayName(),
        'section' => $section_label,
        'has_video' => $has_video,
        'link' => $node->toUrl()->toString(),
        'content_type' => $content_type_name,
        'produccion' => $produccion,
        'nombre_produccion' => $nombre_produccion
      ];
    }

    $build['content_table'] = [
      '#theme' => 'estadisticas_rcn_contenidos',
      '#items' => $items,
      '#baseurl' => 'hola'
    ];

    return $build;
  }
  
  public function ExportCSV() {
      $config = $this->config('estadisticas_rcn.settings');
      $fecha_inicial = $config->get('fecha_inicial');
      $fecha_final = $config->get('fecha_final');
      $content_types = $config->get('content_types');
  
      $fecha_inicial = strtotime($fecha_inicial);
      $fecha_final = strtotime($fecha_final) + (24 * 60 * 60 - 1);
  
      $nids = \Drupal::entityQuery('node')
          ->condition('status', 1)
          ->condition('type', array_keys(array_filter($content_types)), 'IN')
          ->condition('created', [$fecha_inicial, $fecha_final], 'BETWEEN')
          ->sort('created', 'DESC')
          ->execute();
  
      $nodes = Node::loadMultiple($nids);
  
      if (empty($nodes)) {
          return [
              '#markup' => $this->t('No hay contenidos creados entre las fechas seleccionadas.'),
          ];
      }
  
      $items = [];
      foreach ($nodes as $node) {
        
        $section_label = $this->t('No especificado');
        if ($node->hasField('field_seccion') && !$node->get('field_seccion')->isEmpty()) {
          $section_entity = $node->field_seccion->entity;
          $section_label = $section_entity ? $section_entity->getName() : $this->t('No especificado');
        }
        $has_video = $node->hasField('field_nota_con_video') && !$node->get('field_nota_con_video')->isEmpty() ? 'Si' : 'No';

        $produccion = str_replace('_', '-', $node->field_domain_access->target_id);
        $domainStorage = \Drupal::entityTypeManager()->getStorage('domain')->load($node->field_domain_access->target_id);
        $nombre_produccion = $domainStorage->get('name');

        $content_type = $node->bundle();
        $content_type_entity = \Drupal::entityTypeManager()->getStorage('node_type')->load($content_type);
        $content_type_name = $content_type_entity ? $content_type_entity->label() : '';

        $link = 'https://www.canalrcn.com/' . $produccion . ltrim($node->toUrl()->toString(), '/');
        $formatted_date = date('d/m/Y H:i', $node->getCreatedTime());
  
        $items[] = [
          'title' => $node->label(),
          'date' => $formatted_date,
          'author' => $node->getOwner()->getDisplayName(),
          'section' => $section_label,
          'has_video' => $has_video,
          'link' => $link,
          'content_type' => $content_type_name,
        ];
      }

     
  
      $csv_lines = [];
      $headers = ['Title', 'Date', 'Author', 'Section', 'Has Video', 'Link', 'Content Type'];
      $escaped_headers = array_map([$this, 'csv_escape'], $headers);
      $csv_lines[] = implode(',', $escaped_headers);

      foreach ($items as $item) {
          $escaped_line = array_map([$this, 'csv_escape'], $item);
          $csv_lines[] = implode(',', $escaped_line);
      }

      $csv_content = implode("\r\n", $csv_lines);
      $csv_content = mb_convert_encoding($csv_content, 'UTF-8');
      $bom = "\xEF\xBB\xBF"; // BOM para UTF-8
      $csv_content = $bom . $csv_content;

      $response = new Response($csv_content);
      $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
      $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

      return $response;

  }

  private function csv_escape($field) {
    $field_without_commas = str_replace(',', '', $field);
    return $field_without_commas;
  }


}
