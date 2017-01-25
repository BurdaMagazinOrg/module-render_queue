<?php

namespace Drupal\render_queue\Plugin\QueueWorker;

use \Drupal\Core\Queue\QueueWorkerBase;
use \Drupal\Core\Language\LanguageInterface;
use \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;

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
    try {
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder($data['type']);
    }
    catch (InvalidPluginDefinitionException $e) {
      return;
    }
    $enabled_view_modes = \Drupal::service('entity_display.repository')
      ->getViewModeOptionsByBundle($data['type'], $data['bundle']);
    $renderer = \Drupal::service('renderer');
    $entity = \Drupal::entityTypeManager()
      ->getStorage($data['type'])->load($data['id']);
    if (!$entity) {
      return;
    }
    $langcodes = [0 => NULL];
    if ($entity instanceof LanguageInterface) {
      $languages = $entity->getTranslationLanguages();
      $langcodes = array_merge($langcode, array_keys($languages));
    }
    foreach ($enabled_view_modes as $view_mode => $label) {
      foreach ($langcodes as $langcode) {
        $view = $view_builder->view($entity, $view_mode, $langcode);
        $renderer->renderRoot($view);
      }
    }
  }
}
