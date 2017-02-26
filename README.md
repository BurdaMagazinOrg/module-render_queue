WARNING
-------

This module is an experimental approach and still under heavy development,
therefore it's generally not recommended to be used in production.
Currently, there are some critical problems , e.g.:

- If you let the Worker process run via Drush,
  absolute URLs might be wrong generated due to the missing or falsely set address.
- Generating wrong URL might also result in non-working JS, e.g. Infinite Scrolling.
- Image Styles might be generated with root or non-web user ownership,
  which could lead to non-writable folders for web-users.

CONTENTS OF THIS FILE
---------------------
   
 * Introduction
 * Installation
 * Troubleshooting

INTRODUCTION
------------
 
The Render Queue module provides a queue for Drupal,
which renders entities in their view modes.

This module may be useful to reduce performance overheads
during first time loading of content by site visitors.

The first time creation of image style derivatives could drastically increase
the build time on a first page request.
The queue worker therefore also takes care of
which image styles are to be viewed on rendered content,
and generates the corresponding image style derivatives automatically.

The queue may be processed endlessly by the drush command
$ drush render-queue-run

INSTALLATION
------------

Install the module itself as usual, see
https://www.drupal.org/docs/8/extending-drupal-8/installing-contributed-modules-find-import-enable-configure-drupal-8.

After the module has been installed,
you should make sure a worker process is properly set up.

The queue worker is not set up automatically as a cron job,
because it's not suitable to be handled as a cron,
which may be only run a couple of times a day.
You need to manually setup the worker process.

You can do this, e.g. by running
$ drush render-queue-run

You may consider to run above command under a supervisor daemon,
so that the process may be restarted automatically in case of a failure.
Take a look at supervisord (http://supervisord.org/)
as an example supervisor daemon.

See
$ drush help render-queue-run
for more details and available options.

TROUBLESHOOTING
---------------

The queue may grow fast, when the worker process is too slow
or when it's not available.
In this case, you may consider running multiple worker processes
at the same time, even on different machines.

This module is not there to completely solve the problem of first time loading,
but to reduce the amounts of slow server answers.

You should monitor the impact of this module by yourself,
since the results may vary for each individual site.
It's not guaranteed that this module
can significantly improve your site performance.
