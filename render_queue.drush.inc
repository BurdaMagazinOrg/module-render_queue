<?php

/**
 * @file
 * Drush commands for render_queue.
 */
use \Drupal\Core\Queue\QueueInterface;
use \Drupal\Core\Queue\QueueWorkerInterface;
use \Drupal\Core\Queue\RequeueException;
use \Drupal\Core\Queue\SuspendQueueException;

/**
 * Implements hook_drush_command().
 */
function render_queue_drush_command() {
  $commands = array();

  $commands['render-queue-run'] = [
    'description' => dt('Attach a field value to a specific entity or all entities of given type and bundle.'),
    'core' => ['8+'],
    'options' => [
      'time-limit' => dt('The time limit. Default is set to value 0, which is unlimited.'),
    ],
  ];

  return $commands;
}

/**
 * Run the render queue.
 */
function drush_render_queue_run() {
  $time_limit = drush_get_option('time-limit', 0);
  $queue = \Drupal::queue('render_queue');
  $queue_worker = \Drupal::service('plugin.manager.queue_worker')
    ->createInstance('render_queue');

  if ($time_limit) {
    $end = time() + $time_limit;
    while (time() < $end) {
      _render_queue_process_next_item($queue, $queue_worker, $time_limit);
    }
  }
  else {
    while (true) {
      _render_queue_process_next_item($queue, $queue_worker);
    }
  }
  
  drush_print("Finished queue run.");
}

/**
 * Helper function to process the next available item.
 */
function _render_queue_process_next_item(QueueInterface $queue, QueueWorkerInterface $queue_worker, $lease_time = 30) {
  if ($item = $queue->claimItem($lease_time)) {
    try {
      $queue_worker->processItem($item->data);
      $queue->deleteItem($item);
    }
    catch (RequeueException $e) {
      // The worker requested the task be immediately requeued.
      $queue->releaseItem($item);
    }
    catch (SuspendQueueException $e) {
      $queue->releaseItem($item);
      watchdog_exception('cron', $e);
    }
    catch (\Exception $e) {
      watchdog_exception('cron', $e);
    }
  }
}
