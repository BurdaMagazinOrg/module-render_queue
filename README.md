# Render Queue

A queue for Drupal 8 which renders entities in their view modes.

This may be useful to reduce performance overheads during first time loading of content by site visitors.

The queue may be processed endlessly by the drush command
drush render-queue-run

Since the process may die of reasons like exhausted memory,
the command is recommended to be run by a supervisor or as a scheduled tasks which will be executed for a short period of time.
