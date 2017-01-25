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
    $languages = $entity->getTranslationLanguages();
    foreach ($enabled_view_modes as $view_mode => $label) {
      foreach ($languages as $langcode => $language) {
        $view = $view_builder->view($entity, $view_mode, $langcode);
        $renderer->render($view);
      }
    }
  }
}
