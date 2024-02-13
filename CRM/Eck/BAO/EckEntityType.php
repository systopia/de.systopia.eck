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
use Civi\Core\HookInterface;
use Civi\Core\Event\PreEvent;
use Civi\Core\Event\PostEvent;
use Civi\Api4\CustomGroup;
use Civi\Api4\EckEntity;
use Civi\Api4\OptionValue;
use Civi\Api4\Managed;

class CRM_Eck_BAO_EckEntityType extends CRM_Eck_DAO_EckEntityType implements HookInterface {

  /**
   * @return array[]
   */
  public static function getEntityTypes(): array {
    $entityTypes = Civi::cache('metadata')->get('EckEntityTypes');
    if (!is_array($entityTypes)) {
      $entityTypes = CRM_Core_DAO::executeQuery('SELECT * FROM `civicrm_eck_entity_type`')
        ->fetchAll();
      foreach ($entityTypes as &$entityType) {
        $entityType['entity_name'] = 'Eck_' . $entityType['name'];
        $entityType['table_name'] = _eck_get_table_name($entityType['name']);
      }
      Civi::cache('metadata')->set('EckEntityTypes', $entityTypes);
    }
    return $entityTypes;
  }

  /**
   * @param string $name
   * @return array<string>|null
   */
  public static function getEntityType(string $name): ?array {
    foreach (self::getEntityTypes() as $type) {
      if ($type['name'] === $name) {
        return $type;
      }
    }
    return NULL;
  }

  /**
   * @return string[]
   */
  public static function getEntityTypeNames(): array {
    return array_column(self::getEntityTypes(), 'name');
  }

  /**
   * Given an ECKEntityType, make sure data structures are set-up correctly:
   * - the corresponding schema table
   *
   * @param array<string> $entity_type
   *
   * @throws \CRM_Core_Exception
   */
  public static function ensureEntityType(array $entity_type): void {
    $table_name = _eck_get_table_name($entity_type['name']);

    // Ensure table exists.
    CRM_Core_DAO::executeQuery("
      CREATE TABLE IF NOT EXISTS `{$table_name}` (
          `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Eck_{$entity_type['name']} ID',
          `title` text NOT NULL   COMMENT 'The entity title.',
          `subtype` text NOT NULL   COMMENT 'The entity subtype.',
          `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to contact table.',
          `modified_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to contact table.',
          `created_date` timestamp NULL  DEFAULT CURRENT_TIMESTAMP COMMENT 'When the record was created.',
          `modified_date` timestamp NULL  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
              COMMENT 'When the record was last modified.',
          PRIMARY KEY (`id`)
      )
      ENGINE=InnoDB
      DEFAULT CHARSET=utf8
      COLLATE=utf8_unicode_ci;
    ");
  }

  /**
   * Retrieves custom groups extending this entity type.
   *
   * @param $entity_type_name
   *   The name of the entity type to retrieve custom groups for.
   *
   * @return array<int, array>
   */
  public static function getCustomGroups(string $entity_type_name): array {
    return CustomGroup::get(FALSE)
      ->addWhere('extends', '=', 'Eck_' . $entity_type_name)
      ->execute()
      ->getArrayCopy();
  }

  /**
   * Retrieves a list of sub types for the given entity type.
   *
   * @param string $entity_type_name
   *   The name of the entity type to retrieve a list of sub types for.
   * @param bool $as_mapping
   * @return array<int|string,array>
   *   A list of sub types for the given entity type.
   */
  public static function getSubTypes($entity_type_name, $as_mapping = TRUE): array {
    $result = OptionValue::get(FALSE)
      ->addWhere('option_group_id:name', '=', 'eck_sub_types')
      ->addWhere('grouping', '=', $entity_type_name)
      ->execute()
      ->indexBy('value');
    return $as_mapping ?
      $result->column('label') :
      $result->getArrayCopy();
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
    $sub_type = \Civi\Api4\OptionValue::get(FALSE)
      ->addWhere('option_group_id:name', '=', 'eck_sub_types')
      ->addWhere('value', '=', $sub_type_value)
      ->execute()
      ->single();

    // Delete entities of subtype.
    civicrm_api4('Eck_' . $sub_type['grouping'], 'delete', [
      'checkPermissions' => FALSE,
      'where' => [['id', 'IS NOT NULL']],
    ]);

    // TODO: Delete CustomFields in CustomGroup attached to subtype.

    // Delete CustomGroups attached to subtype.
    $custom_groups = array_filter(
      CRM_Eck_BAO_EckEntityType::getCustomGroups($sub_type['grouping']),
      function ($custom_group) use ($sub_type_value) {
        return isset($custom_group['extends_entity_column_value'])
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

  /**
   * Implements hook_civicrm_pre().
   *
   * @see CRM_Utils_Hook::pre()
   * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pre
   */
  public static function self_hook_civicrm_pre(PreEvent $event): void {
    $eckTypeName = $event->id ? CRM_Core_DAO::getFieldValue('CRM_Eck_DAO_EckEntityType', $event->id) : NULL;

    switch ($event->action) {
      case 'create':
        // Replace special characters in the name
        if (!empty($event->params['name'])) {
          $event->params['name'] = CRM_Utils_String::munge($event->params['name'], '_', 52);
        }
        break;

      case 'edit':
        // Do not allow entity type to be renamed, as the table name depends on it
        if (isset($event->params['name']) && $event->params['name'] !== $eckTypeName) {
          throw new RuntimeException('Renaming an EckEntityType is not allowed.');
        }
        break;

      // Perform cleanup before deleting an EckEntityType
      case 'delete':
        // Delete entities of this type.
        EckEntity::delete($eckTypeName, FALSE)
          ->addWhere('id', 'IS NOT NULL')
          ->execute();

        // TODO: Delete custom fields in custom groups extending this entity type?

        // Delete custom groups. This has to be done before removing the table due
        // to FK constraints.
        CustomGroup::delete(FALSE)
          ->addWhere('extends', '=', 'Eck_' . $eckTypeName)
          ->execute();

        // Drop table.
        $table_name = _eck_get_table_name($eckTypeName);
        CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS `{$table_name}`");

        // Delete subtypes.
        OptionValue::delete(FALSE)
          ->addWhere('option_group_id:name', '=', 'eck_sub_types')
          ->addWhere('grouping', '=', $eckTypeName)
          ->execute();
        break;
    }
  }

  /**
   * Implements hook_civicrm_post().
   *
   * @see CRM_Utils_Hook::post()
   * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_post
   */
  public static function self_hook_civicrm_post(PostEvent $event): void {
    // Create tables, etc.
    if ($event->action === 'create') {
      CRM_Eck_BAO_EckEntityType::ensureEntityType($event->object->toArray());
    }

    // Flush schema caches to make the new entity available.
    CRM_Core_DAO_AllCoreTables::flush();
    Civi::cache('metadata')->clear();

    // Refresh managed entities which are autogenerated based on EckEntities
    Managed::reconcile(FALSE)
      ->addModule(E::LONG_NAME)
      ->execute();

    // Flush menu and navigation cache so the new Afform listing page appears.
    CRM_Core_Menu::store();
    CRM_Core_BAO_Navigation::resetNavigation();

    // Flush UF route cache for registering routes in the user framework (CMS).
    $config = CRM_Core_Config::singleton();
    $config->userSystem->invalidateRouteCache();
  }

}
