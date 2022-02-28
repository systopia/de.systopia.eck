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
use Symfony\Component\DependencyInjection\Definition;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function eck_civicrm_config(&$config) {
  _eck_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function eck_civicrm_xmlMenu(&$files) {
  _eck_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function eck_civicrm_install() {
  _eck_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function eck_civicrm_postInstall() {
  _eck_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function eck_civicrm_uninstall() {
  _eck_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function eck_civicrm_enable() {
  _eck_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function eck_civicrm_disable() {
  _eck_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function eck_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _eck_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function eck_civicrm_managed(&$entities) {
  _eck_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function eck_civicrm_caseTypes(&$caseTypes) {
  _eck_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function eck_civicrm_angularModules(&$angularModules) {
  _eck_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function eck_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _eck_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function eck_civicrm_entityTypes(&$entityTypes) {
  _eck_civix_civicrm_entityTypes($entityTypes);

  $eck_entity_types = CRM_Core_DAO::executeQuery(
    'SELECT * FROM `civicrm_eck_entity_type`;'
  )->fetchAll('id');

  foreach ($eck_entity_types as $entity_type) {
    // "CRM_Eck_DAO_*" is a virtual class name, the corresponding class does not
    // exist. "CRM_Eck_DAO_Entity" is therefore defined as the controller
    // class.
    $entityTypes['CRM_Eck_DAO_' . $entity_type['name']] = [
      'name' => 'Eck_' . $entity_type['name'],
      'class' => 'CRM_Eck_DAO_Entity',
      'table' => 'civicrm_eck_' . strtolower($entity_type['name']),
    ];
  }
}

/**
 * Implements hook_civicrm_themes().
 */
function eck_civicrm_themes(&$themes) {
  _eck_civix_civicrm_themes($themes);
}

/**
 * Implements hook_civicrm_container().
 */
function eck_civicrm_container(\Symfony\Component\DependencyInjection\ContainerBuilder $container) {
  // Register API Provider.
  $apiKernelDefinition = $container->getDefinition('civi_api_kernel');
  $apiProviderDefinition = new Definition('Civi\Eck\API\Entity');
  $apiKernelDefinition->addMethodCall('registerApiProvider', array($apiProviderDefinition));
}

/**
 * Implements hook_civicrm_pre().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pre
 */
function eck_civicrm_pre($action, $entity, $id, &$params) {
  if ($entity === 'EckEntityType') {
    $eckTypeName = $id ? CRM_Core_DAO::getFieldValue('CRM_Eck_DAO_EckEntityType', $id) : NULL;

    switch ($action) {
      case 'edit':
        // Do not allow entity type to be renamed, as the table name depends on it
        if (isset($params['name']) && $params['name'] !== $eckTypeName) {
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
}

/**
 * Implements hook_civicrm_post().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_post
 */
function eck_civicrm_post($action, $entity, $id, $object) {
  if ($entity === 'EckEntityType') {
    // Create tables, etc.
    if ($action === 'create') {
      CRM_Eck_BAO_EckEntityType::ensureEntityType($object->toArray());
    }

    // Reset cache of entity types
    Civi::$statics['EckEntityTypes'] = NULL;

    // Flush schema cache.
    CRM_Core_DAO_AllCoreTables::reinitializeCache();
    Civi::cache('metadata')->clear();

    // Flush navigation cache.
    CRM_Core_BAO_Navigation::resetNavigation();
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function eck_civicrm_navigationMenu(&$menu) {
  _eck_civix_insert_navigation_menu($menu, 'Administer/Customize Data and Screens', array(
    'label' => E::ts('ECK Entity Types'),
    'name' => 'eck_entity_types',
    'url' => 'civicrm/admin/eck/entity-types',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
    'icon' => 'fa fa-cubes',
  ));

  _eck_civix_insert_navigation_menu($menu, NULL, array(
    'label' => E::ts('Custom Entities'),
    'name' => 'eck_entities',
    'operator' => 'OR',
    'separator' => 0,
    'icon' => 'fa fa-cubes',
  ));
  foreach (CRM_Eck_BAO_EckEntityType::getEntityTypes() as $entity_type) {
    _eck_civix_insert_navigation_menu($menu, 'eck_entities', array(
      'label' => $entity_type['label'],
      'name' => 'eck_' . $entity_type['name'],
      'url' => 'civicrm/eck/entity/list?reset=1&type=' . $entity_type['name'],
      'permission' => 'access CiviCRM',
      'operator' => 'OR',
      'separator' => 0,
    ));
  }
  _eck_civix_navigationMenu($menu);
}
