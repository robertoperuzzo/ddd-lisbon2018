<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{

  /**
   * Set up the local dev environment ready to start working on.
   */
  public function makeup() {
    /*
     * @todo
     *  - check if containers are already running.
     *  - if task fails stop running next tasks.
     */

    $this->taskExecStack()
      ->stopOnFail()
      ->exec('make up')
      ->exec('docker-compose exec --user 82 php drupal init --destination=/var/www/html/console/ --no-interaction')
      ->run();

    $this->taskFilesystemStack()
      ->stopOnFail()
      ->chmod('web/sites/default', 0755)
      ->symlink('../../../settings.local.php', 'web/sites/default/settings.local.php')
      ->chmod('web/sites/default', 0555)
      ->run();

    /*
     * @todo
     * Installation completes, but http://drupal.docker.localhost:8000/ returns message
     * 'The website encountered an unexpected error. Please try again later.'
     */
    $this->taskExecStack()
      ->exec('docker-compose exec --user 82 php drupal site:install --force --no-interaction')
      ->run();

    $this->taskFilesystemStack()
      ->chmod('web/sites/default/settings.php', 0644)
      ->run();

    $this->taskReplaceInFile('web/sites/default/settings.php')
      ->regex('/\$databases\[([.\S\s]*)\);/i')
      ->to('')
      ->run();

    $this->taskFilesystemStack()
      ->chmod('web/sites/default/settings.php', 0444)
      ->run();
  }

  /**
   * Start docker containers.
   */
  public function start() {
    $this->_exec('make up');
  }

  /**
   * Stop docker containers.
   */
  public function stop() {
    $this->_exec('make stop');
  }

  /**
   * Remove docker containers.
   */
  public function prune() {
    $this->_exec('make prune');
    $this->taskFilesystemStack()
      ->stopOnFail()
      ->chmod('web/sites/default', 0755)
      ->remove('web/sites/default/settings.local.php')
      ->chmod('web/sites/default', 0555)
      ->run();
  }

  /**
   * Drupal core update.
   *
   * @command core-update
   */
  public function drupalCoreUpdate() {
    $this->taskExecStack()
      ->stopOnFail()
      ->exec('docker-compose exec --user 82 php composer update drupal/core --with-dependencies')
      ->exec('docker-compose exec --user 82 php drush updatedb -y')
      ->exec('docker-compose exec --user 82 php drush cr')
      ->run();
  }

}