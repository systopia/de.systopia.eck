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

  /**
   * @return array<string,array<string>>
   * @throws \Exception
   */
  public static function getPermissions(): array {
    $permissions = [];

    $permissions[self::ADMINISTER_ECK_ENTITY_TYPES] = [
      'label' => E::ts('Entity Construction Kit (ECK): Administer Entity Types'),
      'description' => E::ts('Allows creating, editing and deleting custom entity types.'),
    ];

    $permissions[self::ADMINISTER_ECK_ENTITIES] = [
      'label' => E::ts('Entity Construction Kit (ECK): Administer custom entities'),
      'description' => E::ts('Allows creating, viewing, editing and deleting custom entities of any type.'),
    ];
    $permissions[self::VIEW_ANY_ECK_ENTITY] = [
      'label' => E::ts('Entity Construction Kit (ECK): View any custom entity'),
      'description' => E::ts('Allows viewing custom entities of any type.'),
    ];
    $permissions[self::EDIT_ANY_ECK_ENTITY] = [
      'label' => E::ts('Entity Construction Kit (ECK): Create or edit any custom entity'),
      'description' => E::ts('Allows creating and editing custom entities of any type.'),
    ];
    $permissions[self::DELETE_ANY_ECK_ENTITY] = [
      'label' => E::ts('Entity Construction Kit (ECK): Delete any custom entity'),
      'description' => E::ts('Allows deleting custom entities of any type.'),
    ];

    foreach (\CRM_Eck_BAO_EckEntityType::getEntityTypes() as $entityType) {
      $permissions[self::getTypePermissionName(self::ACTION_VIEW, $entityType['name'])] = [
        'label' => E::ts('Entity Construction Kit (ECK): View custom entities of type %1', [1 => $entityType['label']]),
        'description' => E::ts('Allows viewing custom entities of type %1.', [1 => $entityType['label']]),
      ];
      $permissions[self::getTypePermissionName(self::ACTION_EDIT, $entityType['name'])] = [
        'label' => E::ts('Entity Construction Kit (ECK): Create or edit custom entities of type %1', [1 => $entityType['label']]),
        'description' => E::ts('Allows creating and editing custom entities of type %1.', [1 => $entityType['label']]),
      ];
      $permissions[self::getTypePermissionName(self::ACTION_DELETE, $entityType['name'])] = [
        'label' => E::ts('Entity Construction Kit (ECK): Delete custom entities of type %1', [1 => $entityType['label']]),
        'description' => E::ts('Allows deleting custom entities of type %1.', [1 => $entityType['label']]),
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
    if (!in_array($op, [self::ACTION_VIEW, self::ACTION_EDIT, self::ACTION_DELETE], TRUE)) {
      throw new \CRM_Core_Exception("Invalid operation for ECK entity type-specific permission: {$op}.");
    }
    if (NULL === \CRM_Eck_BAO_EckEntityType::getEntityType($type)) {
      throw new \CRM_Core_Exception("Invalid ECK entity type name for type-specific permission: {$type}.");
    }
    return "{$op} eck entity {$type}";
  }

}
