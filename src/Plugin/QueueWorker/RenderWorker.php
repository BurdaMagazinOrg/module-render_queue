<?php

namespace Drupal\render_queue\Plugin\QueueWorker;

use \Drupal\Core\Queue\QueueWorkerBase;

/**
 * @QueueWorker(
 *   id = "render_queue",
 *   title = @Translation("Render queue")
 * )
 */
class RenderWorker extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder($data['type']);
    $enabled_view_modes = \Drupal::service('entity_display.repository')
      ->getViewModeOptionsByBundle($data['type'], $data['bundle']);
    $renderer = \Drupal::service('renderer')->render($element);
    $entity = \Drupal::entityTypeManager()
      ->getStorage($data['type'])->load($data['id']);
    foreach ($enabled_view_modes as $display_id => $label) {
      // TODO View per language.
      $view = $view_builder->view($entity, $display_id);
      $renderer->render($view);
    }
  }
}
