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

namespace Civi\Eck\API;

use CRM_Eck_ExtensionUtil as E;
use Civi\API\Events;
use Civi\Core\Event\GenericHookEvent;
use Civi\API\Provider\ProviderInterface as API_ProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Entity implements API_ProviderInterface, EventSubscriberInterface {

  /**
   * @return callable[]
   */
  public static function getSubscribedEvents():array {
    return [
      'civi.api4.entityTypes' => ['onApi4EntityTypes', Events::W_EARLY],
      'civi.afform_admin.metadata' => 'afformEntityTypes',
      'civi.afform.get' => 'getEckAfforms',
    ];
  }

  /**
   * Not needed for APIv4
   * @param int $version
   * @return array
   */
  public function getEntityNames($version) {
    return [];
  }

  /**
   * Not needed for APIv4
   * @param int $version
   * @param string $entity
   * @return array
   */
  public function getActionNames($version, $entity) {
    return [];
  }

  /**
   * Register each EckEntityType as an APIv4 entity.
   *
   * Callback for `civi.api4.entityTypes` event.
   *
   * @param GenericHookEvent $event
   */
  public function onApi4EntityTypes(GenericHookEvent $event) {
    foreach (\CRM_Eck_BAO_EckEntityType::getEntityTypes() as $entity_type) {
      $event->entities[$entity_type['entity_name']] = [
        'name' => $entity_type['entity_name'],
        'title' => $entity_type['label'],
        'title_plural' => $entity_type['label'],
        'description' => ts('Entity Construction Kit entity type %1', [1 => $entity_type['label']]),
        'primary_key' => ['id'],
        'type' => ['EckEntity'],
        'dao' => 'CRM_Eck_DAO_Entity',
        'table_name' => $entity_type['table_name'],
        'class_args' => [$entity_type['name']],
        'label_field' => 'title',
        'icon_field' => ['subtype:icon'],
        'searchable' => 'secondary',
        'paths' => [
          'browse' => "civicrm/eck/entity/list/{$entity_type['name']}",
          'view' => "civicrm/eck/entity?reset=1&action=view&type={$entity_type['name']}&id=[id]",
          'update' => "civicrm/eck/entity/edit/{$entity_type['name']}/[subtype:name]#?{$entity_type['entity_name']}=[id]",
          'add' => "civicrm/eck/entity/edit/{$entity_type['name']}/[subtype:name]",
        ],
        'class' => 'Civi\Api4\EckEntity',
        'icon' => $entity_type['icon'] ?? 'fa-cubes',
      ];
    }
  }

  /**
   * Make ECK entities available to Form Builder
   *
   * @param GenericHookEvent $e
   */
  public static function afformEntityTypes(GenericHookEvent $e) {
    foreach (\CRM_Eck_BAO_EckEntityType::getEntityTypes() as $entityType) {
      $e->entities[$entityType['entity_name']] = [
        'entity' => $entityType['entity_name'],
        'label' => $entityType['label'],
        'icon' => $entityType['icon'],
        'type' => 'primary',
        'defaults' => '{}',
      ];
    }
  }

  /**
   * Generates afforms for each ECK entity type and sub-type.
   *
   * @param \Civi\Core\Event\GenericHookEvent $event
   * @throws \API_Exception
   */
  public static function getEckAfforms($event) {
    // Early return if forms are not requested
    if ($event->getTypes && !in_array('form', $event->getTypes, TRUE)) {
      return;
    }

    $afforms =& $event->afforms;
    $getNames = $event->getNames;

    // Early return if this api call is fetching afforms by name and those names are not eck-related
    if (
      (!empty($getNames['name']) && !strstr(implode(' ', $getNames['name']), 'Eck_'))
      || (!empty($getNames['module_name']) && !strstr(implode(' ', $getNames['module_name']), 'Eck'))
      || (!empty($getNames['directive_name']) && !strstr(implode(' ', $getNames['directive_name']), 'eck'))
    ) {
      return;
    }

    foreach (\CRM_Eck_BAO_EckEntityType::getEntityTypes() as $entityType) {
      $subTypes = \CRM_Eck_BAO_EckEntityType::getSubTypes($entityType['name'], FALSE);

      // Submission form to create/edit each sub-type
      foreach ($subTypes as $subType) {
        $name = 'afform' . $entityType['entity_name'] . '_' . $subType['name'];
        $item = [
          'name' => $name,
          'type' => 'form',
          'title' => $entityType['label'] . ' (' . $subType['label'] . ')',
          'description' => '',
          'base_module' => E::LONG_NAME,
          'is_dashlet' => FALSE,
          'is_public' => FALSE,
          'is_token' => FALSE,
          'permission' => 'access CiviCRM',
          'server_route' => "civicrm/eck/entity/edit/{$entityType['name']}/{$subType['name']}",
        ];
        if ($event->getLayout) {
          $fields = \civicrm_api4($entityType['entity_name'], 'getFields', [
            'values' => ['subtype' => $subType['value']],
            'select' => ['name'],
            'where' => [
              ['readonly', 'IS EMPTY'],
              ['input_type', 'IS NOT EMPTY'],
              // Don't allow subtype to be changed on the form, since this form is specific to subtype
              ['name', '!=', 'subtype'],
            ],
          ]);
          $item['layout'] = \CRM_Core_Smarty::singleton()->fetchWith('ang/afformEck.tpl', [
            'entityType' => $entityType,
            'subType' => $subType,
            'fields' => $fields,
          ]);
        }
        $afforms[$name] = $item;
      }

      // Search listing for for each type
      $name = 'afsearch' . $entityType['entity_name'] . '_listing';
      $item = [
        'name' => $name,
        'type' => 'search',
        'title' => $entityType['label'],
        'description' => E::ts('Search listing for %1', [1 => $entityType['label']]),
        'base_module' => E::LONG_NAME,
        'is_dashlet' => FALSE,
        'is_public' => FALSE,
        'is_token' => FALSE,
        'permission' => 'access CiviCRM',
        'server_route' => "civicrm/eck/entity/list/{$entityType['name']}",
        'requires' => ['crmSearchDisplayTable'],
      ];
      $item['layout'] = \CRM_Core_Smarty::singleton()->fetchWith('ang/afsearch_eck_listing.tpl', [
        'entityType' => $entityType,
        'subTypes' => $subTypes,
      ]);
      $afforms[$name] = $item;
    }
  }

  /**
   * Not needed for APIv4
   */
  public function invoke($apiRequest) {
  }

}
