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

namespace Civi\Eck;

use CRM_Eck_ExtensionUtil as E;

class Permissions {

  public const ACTION_VIEW = 'view';

  public const ACTION_EDIT = 'edit';

  public const ACTION_DELETE = 'delete';

  public const ACCESS_CIVICRM = 'access CiviCRM';

  public const ADMINISTER_CIVICRM = 'administer CiviCRM';

  public const ADMINISTER_ECK_ENTITY_TYPES = 'administer eck entity types';

  public const ADMINISTER_ECK_ENTITIES = 'administer eck entities';

  public const VIEW_ANY_ECK_ENTITY = 'view any eck entity';

  public const EDIT_ANY_ECK_ENTITY = 'edit any eck entity';

  public const DELETE_ANY_ECK_ENTITY = 'delete any eck entity';

  public static function getPermissions(): array {
    $permissions = [];

    $permission[self::ADMINISTER_ECK_ENTITY_TYPES] = [
      E::ts('Administer Entity Construction Kit (ECK)'),
      E::ts('Allows creating, editing and deleting custom entity types.')
    ];

    $permission[self::ADMINISTER_ECK_ENTITIES] = [
      E::ts('Administer custom entities'),
      E::ts('Allows creating, viewing, editing and deleting custom entities of any type.'),
    ];
    $permission[self::VIEW_ANY_ECK_ENTITY] = [
      E::ts('View any custom entity'),
      E::ts('Allows viewing custom entities of any type.'),
    ];
    $permission[self::EDIT_ANY_ECK_ENTITY] = [
      E::ts('Create or edit any custom entity'),
      E::ts('Allows creating and editing custom entities of any type.'),
    ];
    $permission[self::DELETE_ANY_ECK_ENTITY] = [
      E::ts('Delete any custom entity'),
      E::ts('Allows deleting custom entities of any type.'),
    ];

    foreach (CRM_Eck_BAO_EckEntityType::getEntityTypes() as $entityType) {
      $permission[self::getTypePermissionName(self::ACTION_VIEW, $entityType['name'])] = [
        E::ts('View custom entities of type %1', [1 => $entityType['label']]),
        E::ts('Allows viewing custom entities of type %1.', [1 => $entityType['label']]),
      ];
      $permission[self::getTypePermissionName(self::ACTION_EDIT, $entityType['name'])] = [
        E::ts('Create or edit custom entities of type %1', [1 => $entityType['label']]),
        E::ts('Allows creating and editing custom entities of type %1.', [1 => $entityType['label']]),
      ];
      $permission[self::getTypePermissionName(self::ACTION_DELETE, $entityType['name'])] = [
        E::ts('Delete custom entities of type %1', [1 => $entityType['label']]),
        E::ts('Allows deleting custom entities of type %1.', [1 => $entityType['label']]),
      ];
    }

    return $permissions;
  }

  /**
   * Generates ECK entity type-specific permission names for a given operation.
   *
   * @param string $op
   *   One of "view", "edit", "delete".
   * @param string $type
   *   The name of an ECK entity type.
   *
   * @return string
   *   The permission name.
   * @throws \Exception
   *   When either the operation or the entity type name is invalid.
   */
  public static function getTypePermissionName(string $op, string $type): string {
    if (!in_array($op, [self::ACTION_VIEW, self::ACTION_EDIT, self::ACTION_DELETE])) {
      throw new \Exception("Invalid operation for ECK entity type-specific permission: {$op}.");
    }
    if (!\CRM_Eck_BAO_EckEntityType::getEntityType($type)) {
      throw new \Exception("Invalid ECK entity type name for type-specific permission: {$type}.");
    }
    return "{$op} eck entity {$type}";
  }

}
