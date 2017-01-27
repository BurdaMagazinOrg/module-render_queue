<?php

namespace Drupal\render_queue\Plugin\QueueWorker;

use \Drupal\Core\Queue\QueueWorkerBase;
use \Drupal\Core\Language\LanguageInterface;
use \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use \Drupal\Component\Utility\Html;
use \Drupal\image\Controller\ImageStyleDownloadController;

use \Symfony\Component\HttpFoundation\Request;

/**
 * @QueueWorker(
 *   id = "render_queue",
 *   title = @Translation("Render queue")
 * )
 */
final class RenderWorker extends QueueWorkerBase {

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
        $html = (string) $renderer->renderRoot($view);
        $this->createImageDerivatives($html);
      }
    }
  }

  /**
   * Fetches all image URLs to generate the image styles.
   */
  private function createImageDerivatives($html) {
    $dom = Html::load($html);
    $imgs = $dom->getElementsByTagName('img');
    foreach ($imgs as $img) {
      $url = !empty($img->getAttribute('src')) ? $img->getAttribute('src') : $img->getAttribute('srcset');
      if (empty($url)) {
        continue;
      }
      $url = str_replace('http://', '/', $url);
      $url = str_replace('https://', '/', $url);
      $request = Request::create($url);
      $router = \Drupal::service('router.no_access_checks');
      $match = [];
      try {
        $match = $router->matchRequest($request);
      }
      catch (\Exception $e) {
        continue;
      }
      $controller = ImageStyleDownloadController::create(\Drupal::getContainer());
      $controller->deliver($request, $match['scheme'], $match['image_style']);
    }
  }
}
