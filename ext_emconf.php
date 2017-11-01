<?php
$EM_CONF[$_EXTKEY] = array(
  'title' => 'Algolia Search Extension',
  'description' => 'Algolia Search Extension',
  'category' => 'Search Extension',
  'author' => 'Martin Hummer',
  'author_email' => 'ma.hummer@gmail.com',
  'shy' => '',
  'priority' => '',
  'module' => '',
  'state' => 'stable',
  'internal' => '',
  'uploadfolder' => '0',
  'createDirs' => '',
  'modify_tables' => '',
  'clearCacheOnLoad' => 0,
  'lockType' => '',
  'version' => '0.0.1',
  'constraints' =>
  array(
    'depends' =>
    array(
      'typo3' => '8.7.0-8.99.99',
    ),
    'conflicts' =>
    array(
    ),
    'suggests' =>
    array(
    ),
  ),
  'autoload' =>
  array(
    'psr-4' =>
    array(
      'Mahu\\SearchAlgolia\\' => 'Classes',
    ),
  ),
  'autoload-dev' =>
  array(
    'psr-4' =>
    array(
      'Mahu\\SearchAlgolia\\Tests\\' => 'Tests',
    ),
  ),
);
