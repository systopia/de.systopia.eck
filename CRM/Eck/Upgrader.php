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
class CRM_Eck_Upgrader extends CRM_Eck_Upgrader_Base {

  /**
   * Performs installation tasks.
   */
  public function install() {
  }

  /**
   * Implements hook_civicrm_upgrade_N().
   *
   * Migrate existing ECK entities to new prefix format ("Eck" -> "Eck_") and
   * add managed entity records for previously unmanaged entities (OptionValues
   * in "cg_extend_objects" OptionGroup and "eck_sub_types" OptionGroup).
   */
  public function upgrade_0010() {
    $this->ctx->log->info('Update ECK entity type name prefix and update managed entities.');

    $oldEntityNames = \Civi\Api4\EckEntityType::get(FALSE)
      ->addSelect('CONCAT("Eck", name) AS old_name')
      ->execute()
      ->column('old_name');

    // Create managed entity records for previously unmanaged OptionValues.
    foreach (CRM_Eck_BAO_EckEntityType::getEntityTypes() as $type) {
      $option_value = \Civi\Api4\OptionValue::update(FALSE)
        ->addWhere('option_group_id.name', '=', 'cg_extend_objects')
        ->addWhere('value', '=', 'Eck' . $type['name'])
        ->addValue('value', 'Eck_' . $type['name'])
        ->execute()
        ->single();
      \Civi\Api4\Managed::create(FALSE)
        ->setValues([
                      'module' => E::LONG_NAME,
                      'name' => 'cg_extends:' . $type['name'],
                      'entity_type' => 'OptionValue',
                      'cleanup' => 'always',
                      'update' => 'always',
                      'entity_id' => $option_value['id'],
                    ])
        ->execute();
    }

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

    // Create managed entity for previously unmanaged eck_sub_types OptionGroup.
    \Civi\Api4\Managed::create(FALSE)
      ->setValues([
                    'module' => E::LONG_NAME,
                    'name' => 'OptionGroup_eck_sub_types',
                    'cleanup' => 'always',
                    'entity_type' => 'OptionGroup',
                    'entity_id' => \Civi\Api4\OptionGroup::get(FALSE)
                      ->addSelect('id')
                      ->addWhere('name', '=', 'eck_sub_types')
                      ->execute()
                      ->single()['id'],
                  ])
      ->execute();

    return TRUE;
  }

}
