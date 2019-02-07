<?php
$EM_CONF[$_EXTKEY] = [
  'title' => 'Algolia Search Extension',
  'description' => 'Algolia Search Extension',
  'category' => 'Search Extension',
  'author' => 'Martin Hummer',
  'author_email' => 'ma.hummer@gmail.com',
  'shy' => '',
  'priority' => '',
  'module' => '',
  'state' => 'beta',
  'internal' => '',
  'uploadfolder' => '0',
  'createDirs' => '',
  'modify_tables' => '',
  'clearCacheOnLoad' => 0,
  'lockType' => '',
  'version' => '2.0.0',
  'constraints' => [
    'depends' => [
      'typo3' => '8.7.0-9.5.99',
      'search_core' => '*'
    ],
    'conflicts' => [
    ],
    'suggests' => [
    ],
  ],
  'autoload' => [
    'psr-4' => [
      'Mahu\\SearchAlgolia\\' => 'Classes',
    ],
  ],
  'autoload-dev' => [
    'psr-4' => [
      'Mahu\\SearchAlgolia\\Tests\\' => 'Tests',
    ],
  ],
];
