<?php
/**
 * PunyApp: https://github.com/polygonplanet/PunyApp
 * @license MIT
 */

/**
 * // Example events
 * $this->event->on('app-initialize', function ($app) {});
 * $this->event->on('app-error', function ($app, $e) {});
 * $this->event->on('app-database-error', function ($app, $error) {});
 * $this->event->on('app-before-validate', function ($app, $rules = array()) {});
 * $this->event->on('app-before-redirect', function ($app, $url) {});
 * $this->event->on('app-before-render', function ($app, $template) {});
 * $this->event->on('app-after-render', function ($app, $template) {});
 * $this->event->on('app-before-render-error', function ($app, $code) {});
 * $this->event->on('app-after-render-error', function ($app, $code) {});
 * $this->event->on('app-before-filter', function ($app, $params = array()) {});
 * $this->event->on('app-after-filter', function ($app, $params = array()) {});
 */

/*
// Handle the database error
$this->event->on('app-database-error', function ($app, $error) {
  if ($app->isDebug()) {
    // Show error message only in debug mode
    echo $app->escapeHTML($error);
  }
});
*/

