<?php

define('VERSION_NUMBER', '1.2.7');

/**
 * Adatok a helyi adatbázis használatához
 */
/*return [
  'DB_HOST' => 'localhost',
  'DB_USER' => 'root',
  'DB_PASS' => '',
  'DB_DATABASE' => 'xbank',
  'DB_DRIVER' => 'mysql',
  'DB_CHARSET' => 'utf8mb4',
  'DB_COLLATION' => 'utf8mb4_unicode_ci',
  'DB_PREFIX' => ''
];*/

/**
 * Adatok az Interserver-en lévő adatbázishoz távoli eléréssel
 */
/*return [
  'DB_HOST' => 'www.verovszki.eu',
  'DB_USER' => 'bluehair_xbank',
  'DB_PASS' => '4321Xbank5678Remote9',
  'DB_DATABASE' => 'bluehair_xbank3',
  'DB_DRIVER' => 'mysql',
  'DB_CHARSET' => 'utf8mb4',
  'DB_COLLATION' => 'utf8mb4_unicode_ci',
  'DB_PREFIX' => ''
];*/

/**
 * Adatok az Interserver-en lévő adatbázishoz helyi eléréssel
 */
return [
  'DB_HOST' => 'localhost',
  'DB_USER' => 'bluehair_xbank',
  'DB_PASS' => '4321Xbank5678Remote9',
  'DB_DATABASE' => 'bluehair_xbank3',
  'DB_DRIVER' => 'mysql',
  'DB_CHARSET' => 'utf8mb4',
  'DB_COLLATION' => 'utf8mb4_unicode_ci',
  'DB_PREFIX' => ''
];
