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
    $customData = new CRM_Eck_CustomData(E::LONG_NAME);
    $customData->syncOptionGroup(E::path('resources/eck_sub_types.json'));
  }

  /**
   * Implements hook_civicrm_upgrade_N().
   */
  public function upgrade_0010() {
    $this->ctx->log->info('Update ECK entity type name prefix.');

    // Update entries in the "cg_extend_objects" OptionGroup.
    $eck_entity_types = \Civi\Api4\EckEntityType::get()
      ->execute()
      ->indexBy('id')
      ->column('name');
    $eck_entity_types = array_map(function ($value) {
      return 'Eck' . $value;
    }, $eck_entity_types);
    $cg_extend_entity_types = \Civi\Api4\OptionValue::get(FALSE)
      ->addWhere('option_group_id:name', '=', 'cg_extend_objects')
      ->addWhere('value', 'IN', $eck_entity_types)
      ->execute();
    foreach ($cg_extend_entity_types as $cg_extend_entity_type) {
      $cg_extend_entity_type['value'] = 'Eck_' . substr(
          $cg_extend_entity_type['value'],
          strlen('Eck')
        );
      \Civi\Api4\OptionValue::update(FALSE)
        ->addWhere('id', '=', $cg_extend_entity_type['id'])
        ->setValues($cg_extend_entity_type)
        ->execute();
    }

    // Update Custom Groups.
    $custom_groups = \Civi\Api4\CustomGroup::get(FALSE)
      ->addWhere('extends', 'IN', $eck_entity_types)
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

}
