<?php
namespace Drush\Commands\core;

use Drush\Commands\DrushCommands;

class BrowseCommands extends DrushCommands {

  /**
   * Display a link to a given path or open link in a browser.
   *
   * @command browse
   *
   * @param string|null $path Path to open. If omitted, the site front page will be opened.
   * @option string $browser Specify a particular browser (defaults to operating system default). Use --no-browser to suppress opening a browser.
   * @todo conflicts with global option: @option integer $redirect-port The port that the web server is redirected to (e.g. when running within a Vagrant environment).
   * @usage drush browse
   *   Open default web browser (if configured or detected) to the site front page.
   * @usage drush browse node/1
   *   Open web browser to the path node/1.
   * @usage drush @example.prod
   *   Open a browser to the web site specified in a site alias.
   * @usage drush browse --browser=firefox admin
   *   Open Firefox web browser to the path 'admin'.
   * @complete \Drush\Commands\core\BrowseCommands::complete
   * @handle-remote-commands true
   */
  public function browse($path = '', $options = ['browser' => NULL]) {
    // Redispatch if called against a remote-host so a browser is started on the
    // the *local* machine.
    $alias = drush_get_context('DRUSH_TARGET_SITE_ALIAS');
    if (drush_sitealias_is_remote_site($alias)) {
      $site_record = drush_sitealias_get_record($alias);
      $return = drush_invoke_process($site_record, 'browse', [$path], drush_redispatch_get_options(), array('integrate' => TRUE));
      if ($return['error_status']) {
        return drush_set_error('Unable to execute browse command on remote alias.');
      }
      else {
        $link = $return['object'];
      }
    }
    else {
      if (!drush_bootstrap(DRUSH_BOOTSTRAP_DRUPAL_FULL)) {
        // Fail gracefully if unable to bootstrap Drupal. drush_bootstrap() has
        // already logged an error.
        return FALSE;
      }
      $link = drush_url($path, array('absolute' => TRUE));
    }

    drush_start_browser($link);
    return $link;
  }

  /*
   * An argument provider for shell completion.
   */
  static function complete() {
    return ['values' => ['admin', 'admin/content', 'admin/reports', 'admin/structure', 'admin/people', 'admin/modules', 'admin/config']];
  }
}
