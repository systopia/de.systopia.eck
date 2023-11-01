<?php
use CRM_Eck_ExtensionUtil as E;

return [
  [
    'name' => 'Navigation_afsearchECKEntityTypes',
    'entity' => 'Navigation',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'label' => E::ts('ECK Entity Types'),
        'name' => 'afsearchECKEntityTypes',
        'url' => 'civicrm/admin/eck/entity-types',
        'icon' => 'crm-i fa-cubes',
        'permission' => [
          'administer CiviCRM',
        ],
        'permission_operator' => 'AND',
        'parent_id.name' => 'Customize Data and Screens',
        'weight' => 0,
      ],
      'match' => [
        'name',
        'domain_id',
      ],
    ],
  ],
];
