<?php
/*-------------------------------------------------------+
| CiviCRM Entity Construction Kit                        |
| Copyright (C) 2023 SYSTOPIA                            |
| Author: J. Schuppe (schuppe@systopia.de)               |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

declare(strict_types=1);

use CRM_Eck_ExtensionUtil as E;
use Civi\Eck\Permissions;

$items = [
  [
    'name' => 'Navigation__eck_entities',
    'entity' => 'Navigation',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'label' => E::ts('Custom Entities'),
        'name' => 'eck_entities',
        'url' => NULL,
        'icon' => 'crm-i fa-cubes',
        'permission' => [Permissions::ADMINISTER_ECK_ENTITIES],
        'permission_operator' => 'OR',
        'parent_id' => NULL,
        'is_active' => TRUE,
        'has_separator' => 0,
      ],
      'match' => ['name', 'parent_id', 'domain_id'],
    ],
  ],
];

foreach (CRM_Eck_BAO_EckEntityType::getEntityTypes() as $entity_type) {
  $items[] = [
    'name' => 'Navigation__eck_' . $entity_type['name'],
    'entity' => 'Navigation',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' =>  [
      'version' => 4,
      'values' => [
        'label' => $entity_type['label'],
        'name' => 'eck_' . $entity_type['name'],
        'url' => 'civicrm/eck/entity/list/' . $entity_type['name'],
        'permission' => [
          Permissions::ADMINISTER_ECK_ENTITIES,
          Permissions::getTypePermissionName(Permissions::ACTION_VIEW, $entity_type['name']),
        ],
        'permission_operator' => 'OR',
        'has_separator' => 0,
        'icon' => $entity_type['icon'] ? 'crm-i ' . $entity_type['icon'] : NULL,
        'is_active' => TRUE,
        'parent_id.name' => 'eck_entities',
      ],
      'match' => ['name', 'parent_id', 'domain_id'],
    ],
  ];
}

return $items;
