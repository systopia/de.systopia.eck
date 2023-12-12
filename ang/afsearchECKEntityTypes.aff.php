<?php
use CRM_Eck_ExtensionUtil as E;

return [
  'title' => E::ts('ECK Entity Types'),
  'permission' => [
    'administer eck entity types',
  ],
  'type' => 'search',
  'icon' => 'fa-cubes',
  'server_route' => 'civicrm/admin/eck/entity-types',
];
