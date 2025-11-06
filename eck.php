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

require_once 'eck.civix.php';
use CRM_Eck_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function eck_civicrm_config(&$config) {
  _eck_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Returns all ECK entities in EFv2 format.
 */
function eck_civicrm_entityTypes(array &$entityTypes): void {
  $eck_entity_types = CRM_Core_DAO::executeQuery(
    'SELECT * FROM `civicrm_eck_entity_type`;'
  )->fetchAll('id');

  foreach ($eck_entity_types as $entity_type) {
    $entityName = 'Eck_' . $entity_type['name'];
    $entityTypes[$entityName] = [
      'name' => $entityName,
      'class' => 'CRM_Eck_DAO_Entity',
      'table' => _eck_get_table_name($entity_type['name']),
      'module' => E::LONG_NAME,
      'metaProvider' => \Civi\Eck\EckEntityMetaProvider::class,
    ];
  }
}

/**
 * Convert ECK EntityType name to sql table name.
 *
 * @param string $entityTypeName
 * @return string
 */
function _eck_get_table_name(string $entityTypeName): string {
  // SQL table names must be alphanumeric and no longer than 64 characters
  return CRM_Utils_String::munge('civicrm_eck_' . strtolower($entityTypeName), '_', 64);
}
