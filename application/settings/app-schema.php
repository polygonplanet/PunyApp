<?php
/**
 * PunyApp: https://github.com/polygonplanet/PunyApp
 * @license MIT
 */

/**
 * 'created' and 'modified' fields:
 *
 * By defining a 'created' and/or 'modified' field in your database table
 *  as writable fields (varchar(255)),
 * PunyApp will recognize those fields and populate them automatically
 *  whenever a record is created or saved to the database
 *  (unless the data being saved already contains a value for these fields).
 * The 'created' and 'modified' fields will be set to the current time
 *  by milliseconds when the record is initially added.
 * The 'modified' field will be updated with the current time
 *  by milliseconds whenever the existing record is saved.
 */

$schema = array(
  "CREATE TABLE IF NOT EXISTS punyapp_sessions (
    id      varchar(128) NOT NULL default '',
    data    text,
    expires integer default NULL,
    PRIMARY KEY (id)
  )",

  // For pgsql, change field id type to 'serial'.
  "CREATE TABLE IF NOT EXISTS sample (
    id       integer,
    userid   varchar(255),
    email    varchar(255),
    pass     varchar(255),
    created  varchar(255),
    modified varchar(255),
    PRIMARY KEY (id)
  )"
);

