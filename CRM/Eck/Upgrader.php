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

/**
 * Collection of upgrade steps.
 */
class CRM_Eck_Upgrader extends CRM_Extension_Upgrader_Base {

  /**
   * Performs installation tasks.
   */
  public function install(): void {
  }

  /**
   * Implements hook_civicrm_upgrade_N().
   */
  public function upgrade_0010(): bool {
    $this->ctx->log->info('Update ECK entity type name prefix.');

    $oldEntityNames = \Civi\Api4\EckEntityType::get(FALSE)
      ->addSelect('CONCAT("Eck", name) AS old_name')
      ->execute()
      ->column('old_name');

    // Delete old option values: they will be replaced by managed entities
    \Civi\Api4\OptionValue::delete(FALSE)
      ->addWhere('option_group_id:name', '=', 'cg_extend_objects')
      ->addWhere('name', 'IN', $oldEntityNames)
      ->execute();

    // Update Custom Groups.
    $custom_groups = \Civi\Api4\CustomGroup::get(FALSE)
      ->addWhere('extends', 'IN', $oldEntityNames)
      ->execute();
    foreach ($custom_groups as $custom_group) {
      $custom_group['extends'] = 'Eck_' . substr(
          $custom_group['extends'],
          strlen('Eck')
        );
      \Civi\Api4\CustomGroup::update(FALSE)
        ->addWhere('id', '=', $custom_group['id'])
        ->setValues($custom_group)
        ->execute();
    }

    return TRUE;
  }

  /**
   * Implements hook_civicrm_upgrade_N().
   */
  public function upgrade_0011(): bool {
    $entityTypes = CRM_Core_DAO::executeQuery('
        SELECT
            *,
            CONCAT("Eck_", name) AS entity_name,
            CONCAT("civicrm_eck_", LOWER(name)) AS table_name
        FROM `civicrm_eck_entity_type`;
    ')->fetchAll();

    foreach ($entityTypes as $tableName => $entityType) {
      $tableName = $entityType['table_name'];
      $this->ctx->log->info('Add date/contact columns to ' . $entityType['entity_name']);
      CRM_Core_DAO::executeQuery("ALTER TABLE `$tableName`
        ADD COLUMN `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to contact table.'");
      CRM_Core_DAO::executeQuery("ALTER TABLE `$tableName`
        ADD COLUMN `modified_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to contact table.'");
      CRM_Core_DAO::executeQuery("ALTER TABLE `$tableName`
        ADD COLUMN `created_date` timestamp NULL  DEFAULT CURRENT_TIMESTAMP COMMENT 'When the record was created.'");
      CRM_Core_DAO::executeQuery("ALTER TABLE `$tableName`
        ADD COLUMN `modified_date` timestamp NULL  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            COMMENT 'When the record was last modified.'");
    }

    return TRUE;
  }

  /**
   * Implements hook_civicrm_upgrade_N().
   */
  public function upgrade_0012(): bool {
    $this->ctx->log->info('Add icon column to civicrm_eck_entity_type');
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_eck_entity_type`
      ADD COLUMN `icon` varchar(255) DEFAULT NULL COMMENT 'crm-i icon class'");

    return TRUE;
  }

  /**
   * Implements hook_civicrm_upgrade_N().
   */
  public function upgrade_0013(): bool {
    $this->ctx->log->info('Add in_recent column to civicrm_eck_entity_type');
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_eck_entity_type`
      ADD COLUMN `in_recent` tinyint NOT NULL DEFAULT 1
          COMMENT 'Does this entity type get added to the recent items list'");

    return TRUE;
  }

  /**
   * Implements hook_civicrm_upgrade_N().
   *
   * public function upgrade_0014() {
   * $this->ctx->log->info('Update name column in civicrm_eck_entity_type');
   * CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_eck_entity_type`
   * MODIFY COLUMN `name` varchar(58) NOT NULL COMMENT 'The entity type name, also used in the sql table name'");
   *
   * return TRUE;
   * }
   */

  /**
   * Implements hook_civicrm_upgrade_N().
   */
  public function upgrade_0015(): bool {
    $this->ctx->log->info('Update name column in civicrm_eck_entity_type');
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_eck_entity_type`
      MODIFY COLUMN `name` varchar(52) NOT NULL COMMENT 'The entity type name, also used in the sql table name'");

    return TRUE;
  }

}
