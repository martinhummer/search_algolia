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
  'version' => '0.0.1',
  'constraints' => [
    'depends' => [
      'typo3' => '8.7.0-8.99.99',
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
