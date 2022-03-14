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
  public function install() {
  }

  /**
   * Implements hook_civicrm_upgrade_N().
   */
  public function upgrade_0010() {
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

}
