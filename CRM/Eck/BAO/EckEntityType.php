<?php
/*-------------------------------------------------------+
| CiviCRM Entity Construction Kit                        |
| Copyright (C) 2021 SYSTOPIA                            |
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

use CRM_Eck_ExtensionUtil as E;
use \Civi\Core\Event\GenericHookEvent;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CRM_Eck_BAO_EckEntityType extends CRM_Eck_DAO_EckEntityType {

  protected static $_entityTypes;

  public static function getEntityTypes() {
    if (!isset(static::$_entityTypes)) {
      $entity_types = civicrm_api3('EckEntityType', 'get', [], ['limit' => 0])['values'];
      static::$_entityTypes = array_combine(
        array_map(
          function ($name) {
            return 'Eck' . $name;
          },
          array_column($entity_types, 'name')
        ),
        $entity_types
      );
    }
    return static::$_entityTypes;
  }

  public static function getEntityTypeNames() {
    return array_column(self::getEntityTypes(), 'name');
  }

  /**
   * Create a new EckEntityType based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Eck_DAO_EckEntityType|NULL
   *
  public static function create($params) {
    $className = 'CRM_Eck_DAO_EckEntityType';
    $entityName = 'EckEntityType';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

  public static function ensureEntityTypes() {
    // Synchronise cg_extend_objects option group.
    foreach (self::getEntityTypes() as $entity_type) {
      self::ensureEntityType($entity_type);
    }
  }

  /**
   * Given an ECKEntityType name (and optionally its old name, if it is about to
   * be renamed), make sure all data structures are being set-up correctly:
   * - the EckEntityType entity itself
   * - the corresponding schema table
   * - the entry in the "cg_extend_objects" option group
   * - custom groups extending the entity type
   * - subtypes for the entity type
   *
   * @param array $entity_type
   *   The name of the entity type to create or update.
   * @param array | null $old_entity_type
   *   The old name of the entity type to update.
   *
   * @throws \CiviCRM_API3_Exception
   */
  public static function ensureEntityType($entity_type, $old_entity_type = NULL) {
    $table_name = 'civicrm_eck_' . strtolower($entity_type['name']);

    // Update EckEntityType entity.
    civicrm_api3('EckEntityType', 'create', [
      'id' => $old_entity_type['id'] ?? NULL,
      'name' => $entity_type['name'],
      'label' => $entity_type['label'],
    ]);

    if ($old_entity_type) {
      // Retrieve existing option value for custom-field-extendable object.
      $option_value = civicrm_api3('OptionValue', 'getsingle', [
        'option_group_id' => 'cg_extend_objects',
        'value' => 'Eck' . $old_entity_type['name'],
        'name' => 'civicrm_eck_' . strtolower($old_entity_type['name']),
      ]);

      // Rename table.
      $old_table_name = 'civicrm_eck_' . strtolower($old_entity_type['name']);
      if ($old_table_name != $table_name) {
        CRM_Core_DAO::executeQuery(
          "
            RENAME TABLE `{$old_table_name}` TO `{$table_name}`;
            "
        );
      }
    }
    else {
      // Create table.
      CRM_Core_DAO::executeQuery(
        "
          CREATE TABLE IF NOT EXISTS `{$table_name}` (
              `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Eck{$entity_type['name']} ID',
              `title` text NOT NULL   COMMENT 'The entity title.',
              `subtype` text NOT NULL   COMMENT 'The entity subtype.',
              PRIMARY KEY (`id`)
          )
          ENGINE=InnoDB
          DEFAULT CHARSET=utf8
          COLLATE=utf8_unicode_ci;
          "
      );
    }

    // Synchronize cg_extend_objects option values.
    civicrm_api3('OptionValue', 'create', [
      'id' => $old_entity_type ? $option_value['id'] : NULL,
      'option_group_id' => 'cg_extend_objects',
      'label' => $entity_type['label'],
      'value' => 'Eck' . $entity_type['name'],
      /**
       * Call a "virtual" static method on EckEntityType, which is being
       * resolved using a __callStatic() implementation for retrieving a
       * list of subtypes.
       * @see \CRM_Eck_Utils_EckEntityType::__callStatic()
       * @see \CRM_Core_BAO_CustomGroup::getExtendedObjectTypes()
       */
      'description' => "CRM_Eck_Utils_EckEntityType::{$entity_type['name']}.getSubTypes;",
      'name' => 'civicrm_eck_' . strtolower($entity_type['name']),
      'is_reserved' => 1,
    ]);

    if ($old_entity_type) {
      // Synchronise custom groups.
      foreach (CRM_Eck_DAO_EckEntityType::getCustomGroups($old_entity_type['name']) as $custom_group) {
        civicrm_api3(
          'CustomGroup',
          'create',
          [
            'id' => $custom_group['id'],
            'extends' => 'Eck' . $entity_type['name'],
          ]
        );
      }
      // Synchronise subtypes.
      foreach (self::getSubTypes($old_entity_type['name'], FALSE) as $sub_type) {
        civicrm_api3(
          'OptionValue',
          'create',
          [
            'id' => $sub_type['id'],
            'option_group_id' => 'eck_sub_types',
            'grouping' => $entity_type['name'],
          ]
        );
      }
    }

    // Flush schema cache.
    CRM_Core_DAO_AllCoreTables::reinitializeCache();
  }

}
