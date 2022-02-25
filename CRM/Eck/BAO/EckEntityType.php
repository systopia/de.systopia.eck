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

class CRM_Eck_BAO_EckEntityType extends CRM_Eck_DAO_EckEntityType implements \Civi\Test\HookInterface {

  /**
   * @return array[]
   */
  public static function getEntityTypes(): array {
    if (!isset(Civi::$statics['EckEntityTypes'])) {
      Civi::$statics['EckEntityTypes'] = CRM_Core_DAO::executeQuery(
        'SELECT *, CONCAT("Eck_", name) AS entity_name, CONCAT("civicrm_eck_", LOWER(name)) AS table_name FROM `civicrm_eck_entity_type`;'
      )->fetchAll('entity_name');
    }
    return Civi::$statics['EckEntityTypes'];
  }

  /**
   * @return string[]
   */
  public static function getEntityTypeNames(): array {
    return array_column(self::getEntityTypes(), 'name');
  }

  /**
   * Event fired before an action is taken on an EckEntityType.
   * @param \Civi\Core\Event\PreEvent $event
   */
  public static function self_hook_civicrm_pre(\Civi\Core\Event\PreEvent $event) {
    $eckTypeName = $event->id ? self::getFieldValue(parent::class, $event->id) : NULL;

    switch ($event->action) {
      case 'edit':
        // Do not allow entity type to be renamed, as the table name depends on it
        if (isset($event->params['name']) && $event->params['name'] !== $eckTypeName) {
          throw new Exception('Renaming an EckEntityType is not allowed.');
        }
        break;

      // Perform cleanup before deleting an EckEntityType
      case 'delete':
        // Delete entities of this type.
        civicrm_api4('Eck_' . $eckTypeName, 'delete', [
          'checkPermissions' => FALSE,
          'where' => [['id', 'IS NOT NULL']],
        ]);

        // TODO: Delete custom fields in custom groups extending this entity type?

        // Delete custom groups. This has to be done before removing the table due
        // to FK constraints.
        civicrm_api4('CustomGroup', 'delete', [
          'checkPermissions' => FALSE,
          'where' => [['extends', '=', 'Eck_' . $eckTypeName]],
        ]);

        // Drop table.
        $table_name = 'civicrm_eck_' . strtolower($eckTypeName);
        CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS `{$table_name}`");

        // Delete existing option value for custom-field-extendable object.
        civicrm_api4('OptionValue', 'delete', [
          'checkPermissions' => FALSE,
          'where' => [
            ['option_group_id:name', '=', 'cg_extend_objects'],
            ['value', '=', 'Eck_' . $eckTypeName],
          ],
        ]);

        // Delete subtypes.
        civicrm_api4('OptionValue', 'delete', [
          'checkPermissions' => FALSE,
          'where' => [
            ['option_group_id:name', '=', 'eck_sub_types'],
            ['grouping', '=', $eckTypeName],
          ],
        ]);
        break;
    }
  }

  /**
   * Event fired after an action is taken on an EckEntityType.
   * @param \Civi\Core\Event\PostEvent $event
   */
  public static function self_hook_civicrm_post(\Civi\Core\Event\PostEvent $event) {
    if ($event->action === 'create') {
      self::ensureEntityType($event->object->toArray());
    }

    // Reset cache of entity types
    Civi::$statics['EckEntityTypes'] = NULL;

    // Flush schema cache.
    CRM_Core_DAO_AllCoreTables::reinitializeCache();
    Civi::cache('metadata')->clear();

    // Flush navigation cache.
    CRM_Core_BAO_Navigation::resetNavigation();
  }

  /**
   * Given an ECKEntityType, make sure data structures are set-up correctly:
   * - the corresponding schema table
   * - the entry in the "cg_extend_objects" option group
   *
   * @param array $entity_type
   *
   * @throws \API_Exception
   */
  public static function ensureEntityType($entity_type) {
    $table_name = 'civicrm_eck_' . strtolower($entity_type['name']);

    // Ensure table exists.
    CRM_Core_DAO::executeQuery("
      CREATE TABLE IF NOT EXISTS `{$table_name}` (
          `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Eck_{$entity_type['name']} ID',
          `title` text NOT NULL   COMMENT 'The entity title.',
          `subtype` text NOT NULL   COMMENT 'The entity subtype.',
          PRIMARY KEY (`id`)
      )
      ENGINE=InnoDB
      DEFAULT CHARSET=utf8
      COLLATE=utf8_unicode_ci;
    ");

    // Synchronize cg_extend_objects option values.
    \Civi\Api4\OptionValue::save(FALSE)
      ->addRecord([
        'option_group_id:name' => 'cg_extend_objects',
        'label' => $entity_type['label'],
        'value' => 'Eck_' . $entity_type['name'],
        'name' => 'civicrm_eck_' . strtolower($entity_type['name']),
        'is_reserved' => TRUE,
      ])
      ->setMatch(['option_group_id', 'value'])
      ->execute();
  }

  /**
   * Retrieves custom groups extending this entity type.
   *
   * @param $entity_type_name
   *   The name of the entity type to retrieve custom groups for.
   *
   * @return array
   */
  public static function getCustomGroups($entity_type_name):array {
    return (array) civicrm_api4('CustomGroup', 'get', [
      'checkPermissions' => FALSE,
      'where' => [['extends', '=', 'Eck_' . $entity_type_name]],
    ]);
  }

  /**
   * Retrieves a list of sub types for the given entity type.
   *
   * @param string $entity_type_name
   *   The name of the entity type to retrieve a list of sub types for.
   * @param bool $as_mapping
   * @return array
   *   A list of sub types for the given entity type.
   */
  public static function getSubTypes($entity_type_name, $as_mapping = TRUE):array {
    $result = civicrm_api4('OptionValue', 'get', [
      'checkPermissions' => FALSE,
      'where' => [
        ['option_group_id:name', '=', 'eck_sub_types'],
        ['grouping', '=', $entity_type_name],
      ],
    ]);
    return $as_mapping ?
      $result->indexBy('value')->column('label') :
      (array) $result;
  }

  /**
   * Deletes a subtype, which involves:
   * - deleting all entities of this subtype
   * - deleting all custom fields in custom groups attached to this subtype
   * - deleting all custom groups attached to this subtype
   * - deleting the subtype option value from the "eck_sub_types" option group
   *
   * @param $sub_type_value
   *   The value of the subtype in the "eck_sub_types" option group.
   *
   * @throws \Exception
   */
  public static function deleteSubType($sub_type_value) {
    $sub_type = civicrm_api4('OptionValue', 'get', [
      'checkPermissions' => FALSE,
      'where' => [
        ['option_group_id:name', '=', 'eck_sub_types'],
        ['value', '=', $sub_type_value],
      ],
    ]);

    // Delete entities of subtype.
    civicrm_api4($sub_type['grouping'], 'delete', [
      'checkPermissions' => FALSE,
      'where' => [['id', 'IS NOT NULL']],
    ]);

    // TODO: Delete CustomFields in CustomGroup attached to subtype.

    // Delete CustomGroups attached to subtype.
    $custom_groups = array_filter(
      CRM_Eck_BAO_EckEntityType::getCustomGroups($sub_type['grouping']),
      function ($custom_group) use ($sub_type_value) {
        return
          isset($custom_group['extends_entity_column_value'])
          && is_array($custom_group['extends_entity_column_value'])
          && in_array(
            $sub_type_value,
            $custom_group['extends_entity_column_value']
          );
      }
    );
    foreach (CRM_Eck_BAO_EckEntityType::getCustomGroups($sub_type['grouping']) as $custom_group) {
      if (
        isset($custom_group['extends_entity_column_value'])
        && is_array($custom_group['extends_entity_column_value'])
        && in_array(
          $sub_type_value,
          $custom_group['extends_entity_column_value']
        )
      ) {
        civicrm_api4('CustomGroup', 'delete', [
          'checkPermissions' => FALSE,
          'where' => [['id', '=', $custom_group['id']]],
        ]);
      }
    }

    // Delete subtype.
    civicrm_api4('OptionValue', 'delete', [
      'checkPermissions' => FALSE,
      'where' => [['id', '=', $sub_type['id']]],
    ]);
  }

}
