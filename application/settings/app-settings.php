<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 */

$settings = array(
  /**
   * System settings
   */
  'system' => array(
    /**
     * Debug mode
     *
     * true = show errors
     * false = hide errors
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
     * Enter something characters, symbols is possible.
     */
    'salt' => 'ZQJaiPPYn6Tldb2gottKwIDmGiatuSnV'
  ),
  /**
   * Database settings
   */
  'database' => array(
    /**
     * Database engine
     *
     * Available engines: "mysql", "sqlite" and "posql".
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
   * Session settings
   */
  'session' => array(
    /**
     * Session engine
     *
     * Available engines: "mysql", "sqlite" and "posql".
     */
    'engine' => 'sqlite',
    /**
     * Session name (e.g., 'PHPSESSID')
     */
    'name' => 'sessid',
    /**
     * The expiration date of session
     */
    'expirationDate' => 365
  )
);

