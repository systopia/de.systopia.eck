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
 * Magically create DAO classes for every ECK entity on-demand
 */
spl_autoload_register(function ($class) {
  if (str_starts_with($class, 'CRM_Eck_DAO_Entity') && $class !== 'CRM_Eck_DAO_Entity') {
    // phpcs:ignore Drupal.Functions.DiscouragedFunctions.Discouraged
    eval("class $class extends CRM_Core_DAO_Base {}");
  }
});

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
      'class' => 'CRM_Eck_DAO_Entity' . $entity_type['name'],
      'table' => _eck_get_table_name($entity_type['name']),
      'module' => E::LONG_NAME,
      'metaProvider' => \Civi\Eck\EckEntityMetaProvider::class,
    ];
  }
}

/**
 * Implements hook_civicrm_pre().
 *
 * @param string $op
 * @param string $objectName
 * @param int|null $id
 * @param array<string, mixed> $params
 */
function eck_civicrm_pre($op, $objectName, $id, &$params): void {
  // The `modified_id` default_callback only runs on create, so refresh it here.
  // `modified_date` is maintained by MySQL.
  if ('edit' === $op && str_starts_with($objectName, 'Eck_')) {
    $params['modified_id'] ??= CRM_Core_Session::getLoggedInContactID();
  }
}

/**
 * Implements hook_civicrm_post().
 *
 * @param string $op
 * @param string $objectName
 * @param int|null $id
 * @param CRM_Core_DAO|null $objectRef
 * @param array<string, mixed>|null $params
 */
function eck_civicrm_post($op, $objectName, $id, $objectRef = NULL, $params = NULL): void {
  $entityTypeName = Civi\Eck\Utils::getEntityTypeName($objectName);
  // Add the recently created Entity to the list of recently viewed items.
  if (
    isset($entityTypeName)
    && in_array($op, ['create', 'edit'], TRUE)
    && (CRM_Eck_BAO_EckEntityType::getEntityType($entityTypeName)['in_recent'] ?? FALSE)
  ) {
    Civi\Api4\RecentItem::create(FALSE)
      ->addValue('entity_type', $objectName)
      ->addValue('entity_id', $id)
      ->execute();
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

/**
 * Hack to set default value on custom group form.
 *
 * Workaround for older versions of CiviCRM; fixed in core by https://github.com/civicrm/civicrm-core/pull/34456
 * TODO: Remove this after core version requirement is bumped to 6.12
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function eck_civicrm_buildForm($formName, $form): void {
  if ($formName === 'CRM_Custom_Form_Group' && $form->get('action') === CRM_Core_Action::ADD) {
    $extendsVal = \CRM_Utils_Request::retrieve('extends', 'String');
    if (isset($extendsVal)) {
      $form->setDefaults(['extends' => $extendsVal]);
    }
  }
}
