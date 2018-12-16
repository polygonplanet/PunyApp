<?php
/**
 * PunyApp: https://github.com/polygonplanet/PunyApp
 * @license MIT
 */

$settings = array(
  /**
   * System settings
   */
  'system' => array(
    /**
     * Debug mode
     *
     * true:
     *  - Show errors
     *  - Use 'database.debug'
     *
     * false:
     *  - Hide errors
     *  - Use 'database.default'
     */
    'debug' => true,
    /**
     * Internal language
     *
     * e.g., 'en', 'ja' etc.
     */
    'lang' => '',
    /**
     * internal character-code
     *
     * default = utf-8
     */
    'charset' => 'utf-8',
    /**
     * Timezone
     *
     * e.g., 'America/Chicago', 'Asia/Tokyo' etc.
     */
    'timezone' => '',
    /**
     * Application security salt
     * Enter something random characters.
     * Symbols is possible and enables any length.
     */
    'salt' => 'ZQJaiPPYn6Tldb2gottKwIDmGiatuSnV',
    /**
     * Log application error to 'application/storage/logs'
     */
    'logError' => true,
    /**
     * The maximum item counts of log error.
     */
    'logErrorMax' => 200
  ),
  /**
   * Database settings
   */
  'database' => array(
    /**
     * Default configurations
     * This settings to use 'system.debug'=false.
     */
    'default' => array(
      /**
       * Database engine
       *
       * Available engines: "mysql", "pgsql", "sqlite" and "posql".
       */
      'engine' => 'sqlite',
      /**
       * Database internal encoding (default = 'utf8')
       */
      'encoding' => 'utf8',
      /**
       * Database username
       */
      'user' => '',
      /**
       * Database password
       */
      'pass' => '',
      /**
       * Database name
       */
      'dbname' => 'database_name',
      /**
       * Database host
       */
      'host' => 'localhost',
      /**
       * Database port
       */
      'port'=> ''
    ),
    /**
     * Debug configurations
     * This settings to use 'system.debug'=true.
     */
    'debug' => array(
      'engine' => 'sqlite',
      'encoding' => 'utf8',
      'user' => '',
      'pass' => '',
      'dbname' => 'database_name',
      'host' => 'localhost',
      'port'=> ''
    ),
    /**
     * Log SQL query to 'application/storage/logs'
     */
    'logQuery' => true,
    /**
     * The maximum item counts of log query.
     */
    'logQueryMax' => 200
  ),
  /**
   * Session settings
   */
  'session' => array(
    /**
     * Session engine
     *
     * Available engines: "php", "file" and "database".
     */
    'engine' => 'php',
    /**
     * Session cookie name (e.g., 'PHPSESSID')
     */
    'name' => 'sid',
    /**
     * The expiration time (seconds) for session.
     *
     * default = 60*60*1 = 1 hour
     */
    'timeout' => 60 * 60 * 1
  )
);

