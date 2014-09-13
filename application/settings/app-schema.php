<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 */

$schema = array(
  "CREATE TABLE IF NOT EXISTS sample (
    id       INTEGER PRIMARY KEY,
    userId   VARCHAR(255),
    email    VARCHAR(255),
    pass     VARCHAR(255),
    updateAt INTEGER
  )"
);

