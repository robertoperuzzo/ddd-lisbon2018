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
      ->exec('docker-compose exec --user 82 php drupal init --destination=/var/www/html/console/ --autocomplete')
      ->exec('cp -f ./settings.local.php ./web/sites/default/settings.local.php')
      ->run();

    /*
     * @todo
     * Installation completes, but http://drupal.docker.localhost:8000/ returns message
     * 'The website encountered an unexpected error. Please try again later.'
     */
    $this->taskExecStack()
      ->stopOnFail()
      ->exec('docker-compose exec --user 82 php drupal site:install --force --no-interaction')
      ->run();


    /*
     * @todo
     * Class ResourceWatcher not found. Please install henrikbjorn/lurker Composer package
     */
//    $this->taskWatch()
//      ->monitor('settings.local.php', function() {
//        $this->_exec('cp -f ./settings.local.php ./web/sites/default/settings.local.php');
//      })->run();
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
  }

}